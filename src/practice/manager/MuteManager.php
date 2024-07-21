<?php


namespace practice\manager;


use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use practice\api\InformationAPI;
use practice\Main;
use practice\provider\WorkerProvider;

class MuteManager
{

    public static function iniMuteSQL(\mysqli $db)
    {
        $prep_groups = $db->prepare("CREATE TABLE IF NOT EXISTS `EctaryS4`.`mutes` (
                                              `id_mute` INT NOT NULL AUTO_INCREMENT,
                                              `name_mute` TEXT NOT NULL,
                                              `expire_mute` INT NOT NULL,
                                              `mute` BOOLEAN NOT NULL DEFAULT TRUE,
                                              `reason_mute` VARCHAR(9999) NOT NULL DEFAULT 'No reason',
                                              `author_mute` VARCHAR(999) NOT NULL DEFAULT 'Unknow',
                                              `date_mute` INT NOT NULL DEFAULT 0,
                                              `region_mute` TEXT NOT NULL,
                                              `unmute_status` BOOLEAN NOT NULL DEFAULT FALSE,
                                              `unmute_author` VARCHAR(999) NOT NULL DEFAULT 'Unknow',
                                              PRIMARY KEY(`id_mute`)
                                            ) ENGINE = InnoDB;");

        if (empty($db->error)) {
            $startTime = microtime(true);
            $prep_groups->execute();
            $endTime = microtime(true);
            Main::getInstance()->getLogger()->info("The mutes table has been initialized. (" . round(($endTime - $startTime) * 1000 , 2) . "ms)");
        } else {
            Server::getInstance()->getLogger()->warning("[Practice] " . $db->error);
        }
    }

    public static function mutePlayer($mute_name, $author, $reason, $type, $time, $region)
    {
        $player = Server::getInstance()->getPlayer($mute_name);
        if (!is_null($player))
        {
            $player->sendMessage("Muted");
        }
        SQLManager::sendToWorker(new AsyncMute($mute_name, $author, $reason, $type, $time, $region), WorkerProvider::COMMAND_ASYNC);
    }

    public static function isMute($name)
    {

    }

    public static function addMuteCache($name, $author, $reason, $type, $time)
    {
        PlayerManager::setInformation($name, "expire_mute", $time, false);
        PlayerManager::setInformation($name, "author_mute", $author, false);
        PlayerManager::setInformation($name, "date_mute", time(), false);
        PlayerManager::setInformation($name, "region_mute", InformationAPI::getServerRegion(), false);
        PlayerManager::setInformation($name, "mute", $type, false);
    }
}

class AsyncMute extends AsyncTask
{

    private $region;
    private $time;
    private $type;
    private $author;
    private $mute_name;
    private $reason;

    public function __construct($mute_name, $author, $reason, $type, $time, $region)
    {
        $this->mute_name = $mute_name;
        $this->author = $author;
        $this->type = $type;
        $this->time = $time;
        $this->region = $region;
        $this->reason = $reason;
    }
    public function onRun()
    {
       $db = SQLManager::getSQLSesionAsync();
       $mute = $db->query('SELECT * FROM `mutes` WHERE `name_mute` = "'. $this->mute_name.'"')->fetch_all(MYSQLI_ASSOC);
       if (empty($mute))
       {
           $this->mute($db);
       }else{
            $check = $this->checkMute($mute);
            if (is_bool($check))
            {
                $this->setResult(["type" => "already_mute", $check]);
            }else{
                $this->mute($db);
            }
       }

       $db->close();
    }

    public function onCompletion(Server $server)
    {

    }

    public function checkMute($mutes)
    {
        foreach ($mutes as $mute)
        {
            if ($mute["mute"] == 0)
            {
                if ($mute["expire_mute"] >= time() and $mute["unmute_status"] == 1)
                {
                    continue;
                }else{
                    return $mute;
                }
            }elseif ($mute["mute"] == 1)
            {
                if ($mute["unmute_status"] == 1)
                {
                    continue;
                }else{
                    return $mute;
                }
            }
        }
        return false;
    }

    private function mute($db)
    {
        $db->query('INSERT INTO `mutes`(`name_mute`, `expire_mute`, `mute`, `reason_mute`, `author_mute`, `date_mute`, `region_mute`) VALUES ("'. $this->mute_name.'", '. $this->time.', '. $this->type.', "'. $this->reason.'","'. $this->author.'", '. time().', "'. $this->region.'")');
        $this->setResult(["type" => "mute", ["mute_name" => $this->mute_name, "type" => $this->type, "time_expire" => $this->time, "reason", $this->reason]]);
    }
}

