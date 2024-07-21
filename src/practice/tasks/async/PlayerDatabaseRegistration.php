<?php

namespace practice\tasks\async;

use pocketmine\utils\Internet;
use practice\api\InformationAPI;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use practice\api\KitsAPI;
use practice\api\SyncAPI;
use practice\api\PlayerDataAPI;
use practice\manager\PlayerManager;
use practice\manager\SQLManager;
use practice\manager\TimeManager;
use practice\provider\PermissionProvider;

class PlayerDatabaseRegistration extends AsyncTask
{

    private $player_name;
    private $cache;
    private $os;
    private $ip;
    private $id;

    public function __construct(string $player_name, array $cache, $os, $ip, $id)
    {
        $this->player_name = $player_name;
        $this->cache = (array) $cache;
        $this->os = $os;
        $this->ip = $ip;
        $this->id = $id;
    }

    public function onRun()
    {
        $db = SQLManager::getSQLSesionAsync();
        if (is_null($db)) return $this->setResult(["type" => "error", "error" => "Database error"]);
        $is_ban = $this->isBanned($db);

        if (!is_bool($is_ban)) $this->setResult(["type" => "banned", "info" => ["ban_type" => $is_ban["ban_type"], "ban_expire" => $is_ban["ban_expire"], "ban_reason" => $is_ban["ban_reason"]]]);

        if ($this->playerAlreadyRegister($db))
        {
            $this->setResult(["type" => "exist"]);
        }else{
            $this->setPlayerData($db);
        }
        $db->close();
    }

    public function onCompletion(Server $server)
    {
        $player = Server::getInstance()->getPlayer($this->player_name);
        if (!is_null($player)) {
            switch ($this->getResult()["type"]) {
                case "error":
                    $player->sendMessage("Error : " . $this->getResult()["error"]);
                    break;
                case "good":
                    SQLManager::setPlayerCache($this->player_name, $this->getResult()[0]);
                    $this->initPlayerLoaded($this->player_name);

                    break;
                case "exist":
                    SyncAPI::syncPlayerLocal($this->player_name);
                    break;
                case "banned":
                    $time = ($this->getResult()["info"]["ban_expire"] == 0) ? "Life" : TimeManager::timestampDiffToTime($this->getResult()["info"]["ban_expire"], time());
                    $player->kick("§c§lYOU ARE BANNED§r\n\n§cBan Time » " . $time . "\n§cReason » " . $this->getResult()["info"]["ban_reason"], false, false);
                    break;
            }
        }
    }

    public function setPlayerData($db)
    {
        $db->query('INSERT INTO `players`(`name`, `join_date`) VALUES ("' . $this->player_name . '", ' . time() . ')');
        $cache = (array) $this->cache;
        $cache["join_date"] = time();
        $cache["platform_device"] = $this->os;
        $cache["proxy"] = (Internet::getURL("https://blackbox.ipinfo.app/lookup/" . $this->ip) === "Y") ? 1 : 0;
        $cache["setting"] = PlayerDataAPI::getArraySetting($cache["setting"]);
        $cache["id_device"] = $this->id;
        $cache["ip"] = $this->ip;
        $this->setResult(["type" => "good", $cache]);
    }

    public function playerAlreadyRegister($db): bool
    {
        $player = $db->query('SELECT `id` FROM `players` WHERE `name` = "' . $this->player_name . '"');
        if (!empty($player->fetch_array())) return true;
        return false;
    }

    public function initPlayerLoaded(string $name)
    {
        PlayerManager::$sync_status[$name] = true;
        $player = Server::getInstance()->getPlayer($name);
        $player->sendMessage("§b§lECTARY §r§b» §7Your profile has been successfully created.");
        $player->setImmobile(false);
        SyncAPI::syncPlayerDB($name, "all");
        PermissionProvider::setPlayerPermission($name);
        KitsAPI::addLobbyKit($player);
        SQLManager::mysqlQuerry('UPDATE `players` SET `platform_device` = "' . $this->os . '", `serveur` = "' . InformationAPI::getServerRegion() . '", `proxy` = "' . $this->getResult()[0]["proxy"] . '", `ip` = "' . md5($this->ip) . '", `id_device` = "'. $this->id.'" WHERE `name` = "' . $this->player_name . '" ');
    }

    public function prepareQueryCommand(): string
    {
        $ip = (empty($this->ip)) ? "" : md5($this->ip);
        $ip_sql = (empty($this->ip)) ? "" : ' OR `ban_ip` = "' . $ip . '" ';
        $xuid_sql = (empty($this->xuid)) ? "" : ' OR `ban_xuid` = "' . $this->xuid . '" ';
        $id_device_sql = (empty($this->id)) ? "" : ' OR `ban_id_device` = "' . $this->id . '" ';
        return 'SELECT `ban_type`, `ban_expire`, `ban_reason`, `unban` FROM `bans` WHERE `ban_name` = "' . $this->player_name . '"' . $ip_sql . $xuid_sql . $id_device_sql;
    }

    public function isBanned($db): ?bool
    {
        $ban = $db->query($this->prepareQueryCommand());
        if (!empty($ban)) {
            foreach ($ban->fetch_all(MYSQLI_ASSOC) as $id => $value) {
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
        }
        return false;
    }
}