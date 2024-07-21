<?php

namespace practice\game\event;

use practice\Main;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\level\Position;
use pocketmine\utils\Config;

class Manager
{
    public static $is_running = false;

    public static $is_started = false;

    public static $waiting = false;

    public static $waiting_timer = 60;

    public static $is_ingame = [];

    public static $is_spectating = [];

    public static $player_count = 0;

    public static $event_mode = null;


    public static function setGame(bool $value)
    {
        switch ($value) {
            case true:
                self::$is_running = true;
                break;
            case false:
                self::$is_running = false;
                self::$is_started = false;
                self::$waiting_timer = 60;
                self::$player_count = 0;
                self::$waiting = false;
                tasks\GameTask::$inmatch = false;
                foreach (self::$is_ingame as $ingame => $value) {
                    unset(self::$is_ingame[$ingame]);
                }
                $config = new Config(Main::getInstance()->getDataFolder() . "event.json", Config::JSON);
                $config->setAll([]);
                $config->save();
                break;
        }
    }

    public static function addToGame(Player $player)
    {
        $config = new Config(Main::getInstance()->getDataFolder() . "event.json", Config::JSON);
        $name = $player->getName();
        self::$is_ingame[$name] = true;
        self::$player_count = self::$player_count + 1;
        $config->set($player->getName());
        $config->save();
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        switch (Manager::$event_mode) {
            case "nodebuff":
                $level = Server::getInstance()->getLevelByName("NodebuffE");
                if (!is_null($level)) $player->teleport($level->getSafeSpawn());
                break;
            case "gapple":
                $level = Server::getInstance()->getLevelByName("GappleE");
                if (!is_null($level)) $player->teleport($level->getSafeSpawn());
                break;
            case "sumo":
                $level = Server::getInstance()->getLevelByName("SumoE");
                if (!is_null($level)) $player->teleport($level->getSafeSpawn());
                break;
        }
    }

    public static function removeFromGame(string $name)
    {
        unset(self::$is_ingame[$name]);
        if (self::$player_count !== 0) {
            self::$player_count = self::$player_count - 1;
        }
    }

    public static function addToSpectator(Player $player)
    {
        $name = $player->getName();
        self::$is_spectating[$name] = true;
    }

    public static function removeFromSpectator(Player $player)
    {
        $name = $player->getName();
        unset(self::$is_spectating[$name]);
    }
}