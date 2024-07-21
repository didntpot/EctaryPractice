<?php


namespace practice\commands;


use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use practice\api\InformationAPI;
use practice\manager\BanManager;
use practice\manager\PlayerManager;
use practice\manager\TimeManager;
use practice\provider\WorkerProvider;
use pocketmine\scheduler\AsyncTask;
use practice\manager\SQLManager;
use practice\api\discord\{
    Webhook,
    Message,
    Embed
};

class Ban extends PluginCommand
{

    const T_BAN = 0;
    const BAN = 1;

    public function __construct(Plugin $owner)
    {
        parent::__construct("ban", $owner);
        $this->setDescription("Ban command");
        $this->setPermission("ban.command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$sender->hasPermission($this->getPermission())) return;

        /**
         * $args[0] $args[1] $args[2]
         * (name)   (time)  (reason)
         **/

        if (!isset($args[0])) return $sender->sendMessage("§a» Usage: \n- /ban (name) (time) (reason)\n- /ban (name) (reason)\n- /ban info (name)\n- /ban reason \n  Exemple: /ban (name) $1");
        if ($args[0] === "help") return $sender->sendMessage("§a» Usage: \n- /ban (name) (time) (reason)\n- /ban (name) (reason)\n- /ban info (name)\n- /ban reason \n  Exemple: /ban (name) $1");
        if ($args[0] === "reason") return $sender->sendMessage(BanManager::getAllReasonText());
        if ($args[0] === "info") {
            if (!isset($args[1])) return $sender->sendMessage("§c» Usage: /ban info (name)");
            $name = (!is_null(Server::getInstance()->getPlayer($args[1]))) ? Server::getInstance()->getPlayer($args[1])->getName() : $args[1];
            return SQLManager::sendToWorker(new BanInfoAsync($name, $sender->getName()), WorkerProvider::COMMAND_ASYNC);
        }

        $ban_player = Server::getInstance()->getPlayer($args[0]);
        $ban_name = (!is_null($ban_player)) ? $ban_player->getName() : $args[0];
        $ip = (!is_null($ban_player)) ? $ban_player->getAddress() : "";
        $xuid = (!is_null($ban_player)) ? $ban_player->getXuid() : "";
        $id_device = (isset(PlayerManager::$id_device[$ban_name])) ? PlayerManager::$id_device[$ban_name] : "";
        $region = InformationAPI::getServerRegion();
        $author = $sender->getName();
        $ban_time = time();

        if (isset($args[1]) and strtotime($args[1])) {

            $reason = "";
            for ($i = 2; $i < count($args); $i++) {
                $reason .= $args[$i];
                $reason .= " ";
            }

            $reason = (isset($args[2])) ? (BanManager::hasReason($reason)) ? BanManager::getReasonByNumber($reason) : $reason : "No reason";
            $ban_expire = strtotime($args[1]);
            $ban_type = self::T_BAN;
            $ip = ($ip) ? md5($ip) : "";
            $time = TimeManager::timestampDiffToTime($ban_expire, time());
            BanManager::banPlayer($author, $ban_name, $ban_type, $ban_expire, $ip, $xuid, $id_device, $region, $ban_time, $reason);


        } else {
            $reason = "";
            for ($i = 1; $i < count($args); $i++) {
                $reason .= $args[$i];
                $reason .= " ";
            }
            $reason = (isset($args[1])) ? (BanManager::hasReason($reason)) ? BanManager::getReasonByNumber($reason) : $reason : "No reason";
            $ban_type = self::BAN;
            $ip = ($ip) ? md5($ip) : "";
            $time = "Life";
            BanManager::banPlayer($author, $ban_name, $ban_type, 0, $ip, $xuid, $id_device, $region, $ban_time, $reason);
        }

        $webHook = new Webhook("https://discord.com/api/webhooks/817260064033341471/sdBH9nWEdvbL_w3GvJRibfRspa4l_4xgCwVKRQWJlKajem2rtaivREP8pUZpo97PUT_O");
        $msg = new Message();
        $msg->setUsername("Ectary Network");
        $embed = new Embed();
        $embed->setTitle("Ban");
        $embed->setDescription("**Banned Player:** $ban_name\n**Reason:** $reason\n**Time:** ". $time ."");
        $embed->setFooter("Sender: " . $author);
        $msg->addEmbed($embed);
        $webHook->send($msg);

    }
}

class BanInfoAsync extends AsyncTask
{

    public $name;
    public $sender;

    public function __construct($name, $sender)
    {
        $this->name = $name;
        $this->sender = $sender;
    }

    public function onRun()
    {
        $db = SQLManager::getSQLSesionAsync();
        $result = $db->query('SELECT * FROM `bans` WHERE `ban_name` = "' . $this->name . '"')->fetch_all(MYSQLI_ASSOC);
        if (is_null($result) or empty($result)) {
            $this->setResult(["type" => "no_found"]);
        } else {
            $this->setResult(["type" => "found", "info" => $result]);
        }
        $db->close();
    }

    public function onCompletion(Server $server)
    {
        $player = Server::getInstance()->getPlayer($this->sender);
        if (isset($this->getResult()["info"])) {
            $text = "§a» " . $this->name . " ban history (" . count($this->getResult()["info"]) . "):\n";
            foreach ($this->getResult()["info"] as $data) {
                $time = ($data["ban_expire"] == 0) ? "Life" : TimeManager::timestampDiffToTime($data["ban_temp"], $data["ban_expire"]);
                $color = ($data["ban_expire"] == 0 or $data["ban_expire"] >= time() and $data["unban"] == 0) ? "§c" : "§a";
                $unban = ($data["unban"] == 1) ? "Yes" : "No";
                $unban_name = (!empty($data["unban_name"]) and $data["unban_name"] !== "Unknow") ? "(".$data["unban_name"].")" : "";
                $text .= $color . "- " . $data["ban_name"] . " was banned by " . $data["ban_author"] . " on " . date("j, n, Y", $data["ban_temp"]) . " for \"" . $data["ban_reason"] . "\" | Ban time: " . $time . " | Unbanned : $unban $unban_name\n";
            }
        }

        switch ($this->getResult()["type"]) {
            case "found":
                if (!is_null($player)) {
                    $player->sendMessage($text);
                } else {
                    Server::getInstance()->getLogger()->info($text);
                }
                break;

            case "no_found":
                if (!is_null($player)) {
                    $player->sendMessage("§c» " . $this->name . " no found.");
                } else {
                    Server::getInstance()->getLogger()->info("§c» " . $this->name . " no found.");
                }
                break;
        }
    }
}
