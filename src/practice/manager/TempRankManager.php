<?php


namespace practice\manager;


use pocketmine\scheduler\AsyncTask;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use practice\api\discord\Embed;
use practice\api\discord\Message;
use practice\api\discord\Webhook;
use practice\Main;
use practice\provider\WorkerProvider;

class TempRankManager
{
    public static function initTempRankSQL($db)
    {

        $prep = $db->prepare("CREATE TABLE IF NOT EXISTS `EctaryS4`.`temp_rank`(
                              `id` INT NOT NULL AUTO_INCREMENT,
                              `name` TEXT NOT NULL,
                              `time` INT NULL DEFAULT NULL,
                              `temp_group` TEXT NULL,
                              `old_group` TEXT NULL,
                              PRIMARY KEY(`id`)
                            ) ENGINE = InnoDB;");

        if (empty($db->error)) {
            $startTime = microtime(true);
            $prep->execute();
            $endTime = microtime(true);
            Main::getInstance()->getLogger()->info("The temp rank table has been initialized. (" . round(($endTime - $startTime) * 1000, 2) . "ms)");
        } else {
            Server::getInstance()->getLogger()->warning("[Practice] " . $db->error);
        }
    }

    public static function hasTempGroup($name)
    {
        $config = SQLManager::getPlayerCache($name);
        if (isset($config["temp_group"]) and !is_null($config["temp_group"])) return true;
        return false;
    }

    public static function removeTempGroup($name)
    {
        $config = SQLManager::getPlayerCache($name);
        if (!is_null($config))
        {
            $old = $config["old_group"];

            $config["temp_group"] = null;
            $config["old_group"] = null;
            $config["time"] = null;

            SQLManager::setPlayerCache($name, $config);
            PlayerManager::setInformation($name, "group", $old);
        }
        SQLManager::mysqlQuerry('DELETE FROM `temp_rank` WHERE `name` = "' . $name . '";');
        return false;
    }

    public static function getOldGroup($name)
    {
        $config = SQLManager::getPlayerCache($name);
        if(!is_null($config)) return $config["old_group"];
        return "Basic";
    }

    public static function setOldGroup($name, $old_group)
    {
        $config = SQLManager::getPlayerCache($name);
        $config["old_group"] = $old_group;
        SQLManager::setPlayerCache($name, $config);
    }

    public static function setTempGroup($name, $group, $time)
    {
        $config = SQLManager::getPlayerCache($name);
        $old = (is_null($config)) ? '(SELECT `group` FROM `players` WHERE `name` = "'. $name.'")' : PlayerManager::getInformation($name, "group");
        SQLManager::mysqlQuerry('INSERT INTO temp_rank (name, time, temp_group, old_group) SELECT "' . $name . '", "' . $time . '", "' . $group . '", "' . $old . '" WHERE NOT EXISTS (SELECT name FROM temp_rank WHERE name = "' . $name . '");');

        if (!is_null($config))
        {
            $config["temp_group"] = $group;
            $config["time"] = $time;
            $config["old_group"] = PlayerManager::getInformation($name, "group");
            SQLManager::setPlayerCache($name, $config);
            PlayerManager::setInformation($name, "group", $group);
        }
    }

    public static function getTempGroup($name)
    {
        $config = SQLManager::getPlayerCache($name);
        return $config["temp_group"];
    }

    public static function getTimeGroup($name)
    {
        $config = SQLManager::getPlayerCache($name);
        return $config["time"];
    }

    public static function getTextTimeGroup($name)
    {
        $config = SQLManager::getPlayerCache($name);
        $time = TimeManager::timestampToTime($config["time"]);
        return $time["day"] . "d " . $time["hour"] . "h " . $time["minute"] . "m " . $time["second"] . "s";
    }

    public static function setTimeGroup(string $name, $time)
    {
        $config = SQLManager::getPlayerCache($name);
        $config["time"] = $time;
        SQLManager::setPlayerCache($name, $config);
    }
}


class TempRankTask extends Task
{

    public function onRun(int $currentTick)
    {
        $caches = SQLManager::$cache;
        if (!empty($caches)) {
            foreach ($caches as $name => $cache) {
                if (!isset($cache["time"]) or is_null($cache["time"])) continue;
                if ($cache["time"] <= time()) {
                    $player = Server::getInstance()->getPlayer($name);
                    if (!is_null($player)) $player->sendMessage("§c» Your temporary rank has expired.");

                    $webHook = new Webhook("https://discord.com/api/webhooks/808057470584488017/jOdhzxuC1PikNUsOOfwNBFbzHxnezVY3GyqvBOXz1Ze6Xcgrf3UhKHATKApcHN4wPPlu");
                    $msg = new Message();
                    $msg->setUsername("Ectary Network");
                    $embed = new Embed();
                    $embed->setTitle("Temporary Rank Expired");
                    $embed->setDescription($name." [". $cache["temp_group"]." -> ". $cache["old_group"]." (". TimeManager::timestampDiffToTime(time(), $cache["time"]).") ]");
                    $msg->addEmbed($embed);
                    $webHook->send($msg);
                    TempRankManager::removeTempGroup($name);
                }
            }
        }

    }
}

class TempRankTaskAsync extends Task
{
    public function onRun(int $currentTick)
    {
        Server::getInstance()->getLogger()->info("[Practice] Temporary rank check starting...");
        SQLManager::sendToWorker(new TempRankAsyncTask(), WorkerProvider::SYSTEME_ASYNC);
    }
}


class TempRankAsyncTask extends AsyncTask
{

    public function onRun()
    {
        $db = SQLManager::getSQLSesionAsync();
        $prep = $db->prepare('SELECT `name`, `time`, `old_group`, `temp_group` FROM `temp_rank`');
        if (is_bool($prep)) return $this->setResult(['type' => "error", $db->error]);
        $prep->execute();
        $result = $prep->get_result()->fetch_all(MYSQLI_ASSOC);
        $player = [];
        foreach ($result as $id => $temp_rank) {
            if ($temp_rank["time"] <= time()) $player[] = $temp_rank;
        }
        $this->setResult(["type" => "done", "player" => $player]);
        $db->close();
    }

    public function onCompletion(Server $server)
    {
        switch ($this->getResult()["type"]) {
            case "error":
                Server::getInstance()->getLogger()->info("[EctarySQL] " . $this->getResult()["error"]);
                break;

            case "done":
                if (count($this->getResult()["player"]) != 0)
                {
                    $webHook = new Webhook("https://discord.com/api/webhooks/808057470584488017/jOdhzxuC1PikNUsOOfwNBFbzHxnezVY3GyqvBOXz1Ze6Xcgrf3UhKHATKApcHN4wPPlu");
                    $msg = new Message();
                    $msg->setUsername("Ectary Network");
                    $embed = new Embed();
                    $embed->setTitle("Temporary Rank Expired");
                    $val = "";
                    foreach ($this->getResult()["player"] as $player)
                    {
                        $val .= $player["name"]." [". $player["temp_group"]." -> ". $player["old_group"]." (". TimeManager::timestampDiffToTime(time(), $player["time"]).") ],";
                    }
                    $embed->setDescription(substr_replace($val ,"",-1));
                    $msg->addEmbed($embed);
                    $webHook->send($msg);
                }

                Server::getInstance()->getLogger()->info("[Practice] TempRank check finish... (". count($this->getResult()["player"])." removed)");
                if (!empty($this->getResult()["player"])) {
                    foreach ($this->getResult()["player"] as $player_info) {
                        $player = Server::getInstance()->getPlayer($player_info["name"]);
                        if (!is_null($player)) {
                            if (PlayerManager::isSync($player->getName())) $player->sendMessage("§c» Your temporary rank has expired.");
                        } else {
                            SQLManager::mysqlQuerry('UPDATE players, temp_rank SET players.group = (SELECT `old_group` FROM temp_rank WHERE temp_rank.name = "' . $player_info["name"] . '") WHERE players.name = "' . $player_info["name"] . '"');
                        }
                        TempRankManager::removeTempGroup($player_info["name"]);
                    }
                }
                break;
        }
    }
}