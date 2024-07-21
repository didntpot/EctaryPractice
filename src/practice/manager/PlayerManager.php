<?php

namespace practice\manager;

use pocketmine\entity\Skin;
use practice\api\KitsAPI;
use practice\api\SyncAPI;
use practice\Main;
use practice\provider\PermissionProvider;
use practice\provider\WorkerProvider;
use practice\tasks\async\PlayerDatabaseRegistration;
use practice\items\SplashPotion;
use pocketmine\Player;
use pocketmine\Server;

class PlayerManager
{
    ### GLOBAL DUELS ###
    public static $opoInventory = [];
    public static $lastOpoName = [];
    public static $lastOpoHealth = [];
    public static $duelHits = [];

    ### MLG RUSH ###
    public static $playerTeam = [];
    public static $playerPoints = [];

    ### THE BRIDGE ###
    public static $finished = [];

    ### BOXING ###
    public static $boxingHits = [];

    public static $sync_status = [];

    public static $combat_time = [];

    public static $pearl_time = [];

    public static $need_pearl = [];

    public static $fighter = [];

    public static $pots = [];

    public static $reach = [];

    public static $combo = [];

    public static $os = [];

    public static $ip = [];

    public static $rekit_time = [];

    public static $staff_mode = [];

    public static $frozen = [];

    public static $nickname = [];

    public static $id_device = [];

    public static $uuid = [];

    public static $staff_chat = [];


    public static function getInformation($name, $type)
    {
        if (!is_null(SQLManager::getPlayerCache($name))) {
            if (isset(SQLManager::getPlayerCache($name)[$type])) {
                return SQLManager::getPlayerCache($name)[$type];
            } else {
                return SQLManager::DEFAULT[$type];
            }
        }
        return SQLManager::DEFAULT[$type];
    }

    public static function setInformation($name, $type, $info, $sync = true)
    {
        $config = SQLManager::getPlayerCache($name);
        if (is_null($config)) return;
        $config[$type] = $info;
        SQLManager::setPlayerCache($name, $config);
        if ($sync === true) SyncAPI::syncPlayerDB($name, $type);

        switch ($type) {
            case "group":
                PermissionProvider::setPlayerPermission($name);
                break;
        }
    }

    public static function setCape(Player $player, $cape_name)
    {
        $cape = CapesManager::getCapeByName($cape_name);
        if (is_null($cape)) return null;
        $byte = $cape["cape_bytes"];
        if ($player->hasPermission($cape["permission"])) {
            if (!is_null($byte)) {
                $skin = new Skin($player->getSkin()->getSkinId(), $player->getSkin()->getSkinData(), $byte);
                $player->setSkin($skin);
                $player->sendSkin(Server::getInstance()->getOnlinePlayers());
                PlayerManager::setInformation($player->getName(), "cape_select", $cape["cape_name"]);
            }
        } else {
            PlayerManager::removeCape($player);
        }
    }

    public static function removeCape(Player $player)
    {
        $skin = new Skin($player->getSkin()->getSkinId(), $player->getSkin()->getSkinData(), "");
        $player->setSkin($skin);
        $player->sendSkin(Server::getInstance()->getOnlinePlayers());
    }

    public static function registerPlayer(Player $player)
    {
        $player->setImmobile(true);
        $player->sendMessage("§b§lECTARY §r§b» §7Loading your profile...");
        PlayerManager::$sync_status[$player->getName()] = false;
        KitsAPI::clear($player, "all");

        if (!$player->hasPlayedBefore()) {
            SQLManager::sendToWorker(new PlayerDatabaseRegistration($player->getName(), SQLManager::DEFAULT, PlayerManager::$os[$player->getName()], PlayerManager::$ip[$player->getName()], PlayerManager::$id_device[$player->getName()]), WorkerProvider::SYSTEME_ASYNC);
        } else {
            SyncAPI::syncPlayerLocal($player->getName());
        }

    }

    public static function getPots(Player $player)
    {
        $name = $player->getName();
        self::$pots[$name] = 0;
        $inventory = $player->getInventory();
        foreach ($inventory->getContents() as $item) {
            if ($item instanceof SplashPotion) {
                self::$pots[$name] = self::$pots[$name] + $item->getCount();
            }
        }

        return self::$pots[$name];
    }

    public static function initTablePlayers($db)
    {
        $prep_players = $db->prepare("CREATE TABLE IF NOT EXISTS `EctaryS4`.`players`(
                                              `id` INT NOT NULL AUTO_INCREMENT,
                                              `name` TEXT NOT NULL,
                                              `permissions` TEXT DEFAULT NULL,
                                              `group` VARCHAR(40) DEFAULT '" . SQLManager::DEFAULT["group"] . "',
                                              `language` TEXT(11) DEFAULT NULL,
                                              `kill` INT DEFAULT '0',
                                              `death` INT DEFAULT '0',
                                              `division` VARCHAR(40) DEFAULT '" . SQLManager::DEFAULT["division"] . "',
                                              `elo` INT DEFAULT '1000',
                                              `kill_streak` INT DEFAULT '0',
                                              `wins` INT DEFAULT '0',
                                              `loses` INT DEFAULT '0',
                                              `tags` TEXT DEFAULT NULL,
                                              `coins` INT DEFAULT '0',
                                              `cape_select` TEXT DEFAULT null,
                                              `block_select` TEXT DEFAULT null,  
                                              `setting` TEXT DEFAULT null,                                          
                                              `join_date` INT NOT NULL,
                                              `serveur` TEXT DEFAULT NULL,
                                              `proxy` BOOLEAN DEFAULT 0,
                                              `ip` TEXT DEFAULT NULL,
                                              `platform_device` VARCHAR(30) DEFAULT '" . SQLManager::DEFAULT["platform_device"] . "',
                                              `id_device` TEXT DEFAULT NULL,
                                              PRIMARY KEY(`id`)
                                            ) ENGINE = InnoDB;");

        if (empty($db->error)) {
            $startTime = microtime(true);
            $prep_players->execute();
            $endTime = microtime(true);
            Main::getInstance()->getLogger()->info("The player table has been initialized. (" . round(($endTime - $startTime) * 1000, 2) . "ms)");
        } else {
            Server::getInstance()->getLogger()->warning("[Practice] " . $db->error);
        }
    }

    public static function isSync($name): bool
    {
        if (isset(PlayerManager::$sync_status[$name])) {
            return (PlayerManager::$sync_status[$name] === true) ? false : true;
        } else {
            return true;
        }
    }
}