<?php


namespace practice\manager;


use mysqli;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use practice\Main;
use practice\provider\WorkerProvider;

class BanManager
{
    public static array $reason = ["Cheating", "Warning"];

    public static function initBanSQL($db)
    {
        $prep_groups = $db->prepare('CREATE TABLE `EctaryS4`.`bans`(
                                      `ban_id` INT NOT NULL AUTO_INCREMENT,
                                      `ban_name` TEXT NOT NULL,
                                      `ban_ip` TEXT NOT NULL,
                                      `ban_xuid` TEXT NOT NULL,
                                      `ban_id_device` TEXT NOT NULL,
                                      `ban_region` VARCHAR(9999) DEFAULT "NO_FOUND",
                                      `ban_temp` INT NOT NULL,
                                      `ban_author` TEXT NOT NULL,
                                      `ban_type` BOOLEAN DEFAULT 1,
                                      `ban_expire` INT NOT NULL,
                                      `ban_reason` VARCHAR(9999) DEFAULT "No reason",
                                      `unban` BOOLEAN DEFAULT false,
                                      `unban_name` VARCHAR(9999) DEFAULT "Unknow",
                                      PRIMARY KEY(`ban_id`)
                                    ) ENGINE = InnoDB;');


        if (empty($db->error)) {
            $startTime = microtime(true);
            $prep_groups->execute();
            $endTime = microtime(true);
            Main::getInstance()->getLogger()->info("The bans table has been initialized. (" . round(($endTime - $startTime) * 1000, 2) . "ms)");
        } else {
            Server::getInstance()->getLogger()->warning("[Practice] " . $db->error);
        }
    }

    public static function hasReason($number)
    {
        foreach (self::$reason as $id => $reason) {
            if ($number === "$$id") return true;
        }
        return false;
    }

    public static function getAllReasonText()
    {
        $text = "§a» Reason list:\n";
        foreach (self::$reason as $number => $reason) {
            $text .= "§a[$$number] $reason\n";
        }
        return $text;
    }

    public static function getReasonByNumber($number)
    {
        $number = str_replace("$", "", $number);
        if (!isset(self::$reason[$number])) return "No reason";
        return self::$reason[$number];
    }

    public static function banPlayer($sender, $ban_name, bool $ban_type, int $ban_expire, string $ip, string $xuid, string $id_device, string $region, int $ban_time, $reason = "No reason")
    {
        SQLManager::sendToWorker(new BanManagerAsync(["sender" => $sender,
            "ban_name" => $ban_name,
            "ban_type" => $ban_type,
            "ban_expire" => $ban_expire,
            "ban_ip" => $ip,
            "ban_xuid" => $xuid,
            "ban_id_device" => $id_device,
            "ban_region" => $region,
            "ban_time" => $ban_time,
            "ban_reason" => $reason
        ]), WorkerProvider::COMMAND_ASYNC);
    }
}

class BanManagerAsync extends AsyncTask
{

    /**
     * @var array
     */
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function onRun()
    {
        $db = SQLManager::getSQLSesionAsync();
        $ban_name = $this->data["ban_name"];
        $ip = (empty($this->data["ban_ip"])) ? "" : md5($this->data["ban_ip"]);
        $ban_ip = (empty($this->data["ban_ip"])) ? "" : ' OR `ban_ip` = "' . $ip . '" ';
        $ban_xuid = (empty($this->data["ban_xuid"])) ? "" : ' OR `ban_xuid` = "' . $this->data["ban_xuid"] . '" ';
        $ban_id_device = (empty($this->data["ban_id_device"])) ? "" : ' OR `ban_id_device` = "' . $this->data["ban_id_device"] . '" ';

        $ban = $db->query('SELECT `ban_name`, `ban_type`, `ban_expire`, `ban_author`, `unban` FROM `bans` WHERE `ban_name` = "' . $ban_name . '" ' . $ban_ip . $ban_xuid . $ban_id_device)->fetch_all(MYSQLI_ASSOC);
        if (is_null($ban) or empty($ban)) {
            BanManagerAsync::banPlayer($db);
        } else {
            foreach ($ban as $id => $info) {
                if (($info["ban_type"] === "1" and $info["unban"] == "0") or ($info["ban_expire"] >= time() and $info["unban"] == "0")) {
                    $db->close();
                    $this->setResult(["type" => "already_ban", "info" => $info]);
                    return;
                }
            }
            BanManagerAsync::banPlayer($db);
        }
        $db->close();
    }

    public function onCompletion(Server $server)
    {
        $player = Server::getInstance()->getPlayer($this->data["sender"]);
        switch ($this->getResult()["type"]) {
            case "already_ban":
                $type = ((bool)$this->getResult()["info"]["ban_type"] === true) ? "Ban for life" : date("F j, Y, g:i a", $this->getResult()["info"]["ban_expire"]);
                if (!is_null($player)) {
                    $player->sendMessage("§c» " . $this->data["ban_name"] . " already banned by " . $this->getResult()["info"]["ban_author"] . " ($type)");
                } else {
                    Server::getInstance()->getLogger()->info("§c» " . $this->data["ban_name"] . " already banned by " . $this->getResult()["info"]["ban_author"] . " ($type)");
                }
                break;
            case "good":
                if (!is_null($player)) {
                    if ($this->data["ban_expire"] == 0) {
                        $player->sendMessage("§c» " . $this->data["ban_name"] . " is banned for life for '" . $this->data["ban_reason"] . "'.");
                        Server::getInstance()->broadcastMessage("§c» ". $this->data["ban_name"] ." has been life banned.");
                    } else {
                        $player->sendMessage("§c» " . $this->data["ban_name"] . " is banned until " . date("F j, Y, g:i a", $this->data["ban_expire"]) . " for '" . $this->data["ban_reason"] . "'.");
                        Server::getInstance()->broadcastMessage("§c» ". $this->data["ban_name"] ." has been temporary banned.");
                    }
                } else {
                    if ($this->data["ban_expire"] == 0) {
                        Server::getInstance()->getLogger()->info("§c» " . $this->data["ban_name"] . " is banned for life for '" . $this->data["ban_reason"] . "'.");
                    } else {
                        Server::getInstance()->getLogger()->info("§c» " . $this->data["ban_name"] . " is banned until " . date("F j, Y, g:i a", $this->data["ban_expire"]) . " for '" . $this->data["ban_reason"] . "'.");
                    }
                }
                $player = Server::getInstance()->getPlayer($this->data["ban_name"]);
                $time = ($this->data["ban_expire"] == 0) ? "Life" : TimeManager::timestampDiffToTime($this->data["ban_expire"], $this->data["ban_time"]);
                if (!is_null($player)) $player->kick("§c§lYOU ARE BANNED§r\n\n§cBan Time » " . $time . "\n§cReason » " . $this->data["ban_reason"], false, false);
                break;
        }
    }

    private function banPlayer(mysqli $db)
    {

        $type = ($this->data["ban_type"] === false) ? "0" : "1";
        $device_id = (empty($this->data["ban_id_device"])) ? 'IFNULL((SELECT `id_device` FROM `players` WHERE `name` = "' . $this->data["ban_name"] . '"), "")' : "\"" . $this->data["ban_id_device"] . "\"";
        $ip = (empty($this->data["ban_ip"])) ? 'IFNULL((SELECT `ip` FROM `players` WHERE `name` = "' . $this->data["ban_name"] . '"), "")' : "\"" . $this->data["ban_ip"] . "\"";
        $syntax = 'INSERT INTO
                                      `bans`(
                                        `ban_name`,
                                        `ban_ip`,
                                        `ban_xuid`,
                                        `ban_id_device`,
                                        `ban_region`,
                                        `ban_temp`,
                                        `ban_author`,
                                        `ban_type`,
                                        `ban_expire`,
                                        `ban_reason`
                                      )
                                    VALUES(
                                      "' . $this->data["ban_name"] . '",
                                      ' . $ip . ',
                                      "' . $this->data["ban_xuid"] . '",
                                      ' . $device_id . ',
                                      "' . $this->data["ban_region"] . '",
                                      "' . $this->data["ban_time"] . '",
                                      "' . $this->data["sender"] . '",
                                      "' . $type . '",
                                      "' . $this->data["ban_expire"] . '",
                                      "' . $this->data["ban_reason"] . '"
                                    )';
        $db->query($syntax);
        (empty($db->error)) ? $this->setResult(["type" => "good"]) : $this->setResult(["type" => "error", "info" => $db->error]);
    }
}