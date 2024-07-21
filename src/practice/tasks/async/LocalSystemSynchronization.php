<?php


namespace practice\tasks\async;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;
use practice\api\discord\Embed;
use practice\api\discord\Message;
use practice\api\discord\Webhook;
use practice\api\KitsAPI;
use practice\api\PlayerDataAPI;
use practice\Main;
use practice\manager\LogsManager;
use practice\manager\PlayerManager;
use practice\manager\SQLManager;
use practice\manager\TempRankManager;
use practice\manager\TimeManager;
use practice\provider\PermissionProvider;
use practice\provider\WorkerProvider;
use practice\api\InformationAPI;



class LocalSystemSynchronization extends AsyncTask
{


    private $cache;

    private $os;
    private $ip;
    private $id;
    private $xuid;
    private $player_name;

    public function __construct(string $player_name, array $cache, $os, $ip, $xuid, $id)
    {
        $this->player_name = $player_name;
        $this->cache = $cache;
        $this->os = $os;
        $this->ip = $ip;
        $this->xuid = $xuid;
        $this->id = $id;
    }

    public function onRun()
    {
        $db = SQLManager::getSQLSesionAsync();
        $db->set_charset("utf8");
        if (is_null($db)) return $this->setResult(["type" => "error", "error" => "Database error"]);
        $player_data= $db->query($this->getSyntax());
        $player_data = $player_data->fetch_all(MYSQLI_ASSOC);

        if (!$this->isRegistered($player_data))
        {
            $this->setResult(["type" => "no_register"]);
        }else{
            $is_ban = $this->isBanned($player_data);
            if (!is_bool($is_ban))
            {
                $this->setResult(["type" => "banned", "info" => ["ban_type" => $is_ban["ban_type"], "ban_expire" => $is_ban["ban_expire"], "ban_reason" => $is_ban["ban_reason"]]]);
            }else{
                if ($this->isNotValidPlayer($player_data)) return $this->setResult(["type" => "kick"]);
                $this->setPlayerData($player_data);
            }
        }
        $db->close();
    }


    public function sendAllStaff($message)
    {
        foreach (Server::getInstance()->getOnlinePlayers() as $player)
        {
            if (in_array(PlayerManager::getInformation($player->getName(), "group"), ["Mod", "SeniorMod", "HeadMod", "Admin", "SeniorAdmin", "HeadAdmin", "CoOwner", "Owner", "TrialMod"]) or $player->isOp())
            {
                $player->sendMessage($message);
            }
        }
    }

    public function onCompletion(Server $server)
    {
        if (!is_null(Server::getInstance()->getPlayer($this->player_name))) {
            $player = Server::getInstance()->getPlayer($this->player_name);
            switch ($this->getResult()["type"]) {
                case "good":
                    SQLManager::setPlayerCache($this->player_name, $this->getResult()[0]);
                    if (LogsManager::isLogs()) Main::getInstance()->getLogger()->info($this->player_name . " sync local.");
                    $this->initPlayerLoaded($this->player_name);
                    break;
                case "no_register":
                    SQLManager::sendToWorker(new PlayerDatabaseRegistration($player->getName(), SQLManager::DEFAULT, $this->os, $this->ip, $this->id), WorkerProvider::SYSTEME_ASYNC);
                    break;
                case "error":
                    if (LogsManager::isLogs())
                    {
                        $player->sendMessage("Error : " . $this->getResult()["error"]);
                        Server::getInstance()->getLogger()->info("Error: ". $this->getResult());
                    }
                    break;
                case "banned":
                    $time = ($this->getResult()["info"]["ban_expire"] == 0) ? "Life" : TimeManager::timestampDiffToTime($this->getResult()["info"]["ban_expire"], time());
                    $player->kick("§c§lYOU ARE BANNED§r\n\n§cBan Time » " . $time . "\n§cReason » " . $this->getResult()["info"]["ban_reason"], false, false);
                    break;
                case "kick":
                    $player->kick("§cA problem has been encountered with your username\n§cAn issue? §a§lAdd Matéo#4551", false);
                    $this->sendBadNameDiscord();
                    break;
            }
        }
    }

    public function initPlayerLoaded(string $name)
    {
        SQLManager::mysqlQuerry('UPDATE `players` SET `platform_device` = "' . $this->os . '", `serveur` = "' . InformationAPI::getServerRegion() . '", `proxy` = "' . $this->getResult()[0]["proxy"] . '", `ip` = "' . md5($this->ip) . '", `id_device` = "'. $this->id .'" WHERE `name` = "' . $name . '" ');
        if ($this->getResult()["proxy"] == 1)
        {
            $this->sendAllStaff("§c» ". $this->player_name ." probably uses a vpn.");
            Server::getInstance()->getLogger()->info("§c» ". $this->player_name ." probably uses a vpn.");
        }

        PlayerManager::$sync_status[$name] = true;
        $player = Server::getInstance()->getPlayer($name);
        PermissionProvider::setPlayerPermission($name);
        $player->setImmobile(false);
        PlayerManager::setCape($player, PlayerManager::getInformation($player->getName(), "cape_select"));
        KitsAPI::addLobbyKit($player);
        $player->sendMessage("§b§lECTARY §r§b» §7Your profile has been successfully loaded.");
        if (!is_null(TempRankManager::getTimeGroup($name)) and TempRankManager::getTimeGroup($name) <= time()) TempRankManager::removeTempGroup($name);
    }

    private function getSyntax()
    {
        $ip = (empty($this->ip)) ? "" : md5($this->ip);
        $ip_sql = (empty($ip)) ? "" : ' OR `ban_ip` = "' . $ip . '" ';
        $xuid_sql = (empty($this->xuid)) ? "" : ' OR `ban_xuid` = "' . $this->xuid . '" ';
        $id_device_sql = (empty($this->id)) ? "" : ' OR `ban_id_device` = "' . $this->id . '" ';

        $syntax = 'SELECT
                      players.*,
                      temp_rank.temp_group,
                      temp_rank.old_group,
                      temp_rank.time,
                      bans.ban_type,
                      bans.ban_expire,
                      bans.ban_reason,
                      bans.unban,
                      mutes.name_mute,
                      mutes.expire_mute,
                      mutes.mute,
                      mutes.author_mute,
                      mutes.date_mute,
                      kits.*
                    FROM
                      players
                    LEFT JOIN
                      temp_rank ON temp_rank.name = players.name
                    LEFT JOIN
                      bans ON bans.ban_name = "' . $this->player_name . '"' . $ip_sql . $xuid_sql . $id_device_sql . '
                    LEFT JOIN
                      mutes ON mutes.name_mute = "' . $this->player_name . '"
                    LEFT JOIN
                      kits ON kits.name_kit = "' . $this->player_name . '"
                    WHERE
                      players.name = "' . $this->player_name . '"';
        return $syntax;
    }

    public function isBanned($result)
    {
        foreach ($result as $id => $value) {
            if (!is_null($value["ban_type"]) and in_array($value["ban_type"], [0, 1])) {
                if ($value["ban_type"] == 0) {
                    if ($value["ban_expire"] >= time()) {
                        if ($value["unban"] == 1) continue;
                        return $value;
                    }
                } elseif ($value["ban_type"] == 1) {
                    if ($value["unban"] == 1) continue;
                    return $value;
                }
            }
        }
        return false;
    }

    private function isRegistered($result): bool
    {
        if (empty($result) or (isset($result[0]) and is_null($result[0]["name"]))) return false;
        return true;
    }

    private function isNotValidPlayer($data): bool
    {
        if ($this->player_name !== $data[array_key_first($data)]["name"]) return true;
        return false;
    }

    private function setPlayerData($result)
    {
        foreach ($result[0] as $key => $info) {
            $data[$key] = $info;
        }

        $data["platform_device"] = $this->os;
        $data["proxy"] = (Internet::getURL("https://blackbox.ipinfo.app/lookup/" . $this->ip) === "Y") ? 1 : 0;
        $data["setting"] = PlayerDataAPI::getArraySetting($data["setting"]);
        $data["ip"] = $this->ip;
        $this->setResult(["type" => "good", $data, "proxy" => $data["proxy"]]);
    }

    private function sendBadNameDiscord()
    {
        $webHook = new Webhook("https://discord.com/api/webhooks/728485940917698631/7aNUqSyNaTsMW7NOWs3ZImxzm8iJWnQRqbONywB7T70ZiA8qUZF5TfN79ttIb4CgazpT");
        $msg = new Message();
        $msg->setUsername("Ectary Network");
        $embed = new Embed();
        $embed->setTitle("Username issue");
        $embed->setDescription("DB name: ". $this->getResult()[0][0]."\nPlayer name: ". $this->getResult()[0][1]);
        $msg->addEmbed($embed);
        $webHook->send($msg);
    }

}