<?php

namespace practice\tasks\async;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Config;
use practice\api\PlayerDataAPI;
use practice\api\SyncAPI;
use practice\Main;
use practice\manager\SQLManager;
use practice\provider\PermissionProvider;

class DatabaseSystemSynchronization extends AsyncTask
{

    private $player_name;
    private $cache;
    private $type;
    private $victim;

    public function __construct(string $player_name, $cache, string $type, array $victim = [])
    {
        $this->player_name = $player_name;
        $this->cache = $cache;
        $this->type = $type;
        $this->victim = $victim;
    }

    public function onRun()
    {
        $cache = $this->cache;

        $db = SQLManager::getSQLSesionAsync();
        $db->set_charset("utf8");
        if (is_null($db)) return $this->setResult(["type" => "error", "error" => "Database error"]);

        /** Syncro SYSTEME START **/
        if ($this->type === "permissions") {
            $prep = $db->prepare('UPDATE `players` SET `permissions` = "' . PermissionProvider::implodePermission($cache["permissions"]) . '" WHERE `name` = "' . $this->player_name . '"');
        } elseif ($this->type === "kill_death_streak") {
            $db->query('UPDATE `players` SET `kill_streak` = "' . $cache["kill_streak"] . '" WHERE `name` = "' . $this->player_name . '" ');
            $db->query('UPDATE `players` SET `kill` = "' . $cache["kill"] . '" WHERE `name` = "' . $this->player_name . '" ');
            $db->query('UPDATE `players` SET `kill_streak` = "' . $this->victim["cache"]["kill_streak"] . '" WHERE `name` = "' . $this->victim["name"] . '" ');
            $prep = $db->prepare('UPDATE `players` SET `death` = "' . $this->victim["cache"]["death"] . '" WHERE `name` = "' . $this->victim["name"] . '" ');

        } elseif ($this->type === "all") {
            $ip = (empty($cache["ip"])) ? "" : md5($cache["ip"]);
            $prep = $db->prepare('UPDATE `players`
                                SET
                                  `permissions` = "' . $cache["permissions"] . '",
                                  `group` = "' . $cache["group"] . '",
                                  `language` = "' . $cache["language"] . '",
                                  `kill` = "' . $cache["kill"] . '",
                                  `death` = "' . $cache["death"] . '",
                                  `division` = "' . $cache["division"] . '",
                                  `tags` = "' . $cache["tags"] . '",
                                  `coins` = "' . $cache["coins"] . '",
                                  `elo` = "' . $cache["elo"] . '",
                                  `kill_streak` = "' . $cache["kill_streak"] . '",
                                  `wins` = "' . $cache["wins"] . '",
                                  `loses` = "' . $cache["loses"] . '",
                                  `cape_select` = "' . $cache["cape_select"] . '",
                                  `block_select` = "' . $cache["block_select"] . '",
                                  `setting` = "' . PlayerDataAPI::getStringSetting($cache["setting"]) . '",
                                  `join_date` = "' . $cache["join_date"] . '",
                                  `serveur` = "' . $cache["serveur"] . '",
                                  `proxy` = "' . $cache["proxy"] . '",
                                  `ip` = "' . $ip . '",
                                  `platform_device` = "' . $cache["platform_device"] . '",
                                  `id_device` = "' . $cache["id_device"] . '"
                                WHERE `name` = "' . $this->player_name . '" ');
        } elseif ($this->type === "setting") {
            $prep = $db->prepare('UPDATE `players` SET `setting` = "' . PlayerDataAPI::getStringSetting($cache["setting"]) . '" WHERE `name` = "' . $this->player_name . '"');
        } else {
            $type = $this->type;
            $prep = $db->prepare('UPDATE `players` SET `' . $type . '` = "' . $cache[$type] . '" WHERE `name` = "' . $this->player_name . '"');
        }

        /** Syncro SYSTEME END**/
        if (!empty($db->error_list)) {
            $this->setResult(["type" => "error", "error" => $db->error]);
        } else {
            $this->setResult(["type" => "good"]);
            $prep->execute();
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
                    if (isset($this->victim["name"])) return Main::getInstance()->getLogger()->info($this->player_name . "/" . $this->victim["name"] . " sync database (" . $this->type . ").");
                    Main::getInstance()->getLogger()->info($this->player_name . " sync database (" . $this->type . ").");
                    break;
            }
        } else {
            Main::getInstance()->getLogger()->info($this->player_name . " sync database (" . $this->type . ").");
        }
    }
}