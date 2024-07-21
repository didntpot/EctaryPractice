<?php

namespace practice\game\events;

use practice\scoreboard\{PreEventScoreboard};
use pocketmine\Player;
use pocketmine\Server;
use practice\events\listener\PlayerJoin;

class EventManager
{
    CONST NODEBUFF_LEVEL = "NodebuffE";
    CONST GAPPLE_LEVEL = "GappleE";
    CONST SUMO_LEVEL = "SumoE";

    public static $isStarted;
    public static $isWaiting;
    public static $inMatch = false;
    public static $eventType;

    public static $playerCount = 0;
    public static $eliminatedCount = 0;
    public static $roundCount = 0;

    public static $players = [];
    public static $isEliminated = [];

    public static $waitingTime = 120;

    public static $playerOne;
    public static $playerTwo;

    public static function start(string $type)
    {
        self::$isStarted = true;
        self::$isWaiting = true;
        self::$eventType = $type;
    }

    public static function stop()
    {
        self::$isStarted = false;
        self::$isWaiting = false;
        self::$eventType = "";
        self::$playerCount = 0;
        self::$eliminatedCount = 0;
        self::$roundCount = 0;

        foreach(self::$players as $playerName => $value)
        {
            unset(self::$players[$playerName]);
        }
    }

    public static function add(Player $player)
    {
        if(self::$isWaiting == false) return $player->sendMessage("§c» This event has already started or no events are currently running.");
        if(self::$playerCount > 49) return $player->sendMessage("§c» This event is currently full.");

        $name = $player->getName();

        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->removeAllEffects();

        self::$playerCount = self::$playerCount+1;

        self::$players[$name] = true;
        self::$isEliminated[$name] = false;

        self::sendMessage("§a» {$player->getName()} has joined the event. §7(".self::$playerCount."/50)");

        unset(PlayerJoin::$scoreboard[$player->getName()]);
        PlayerJoin::$scoreboard[$player->getName()] = new PreEventScoreboard($player);
        PlayerJoin::$scoreboard[$player->getName()]->sendRemoveObjectivePacket();
        PreEventScoreboard::createLines($player);

        switch(self::$eventType)
        {
            case "nodebuff":
                $player->teleport(Server::getInstance()->getLevelByName(self::NODEBUFF_LEVEL)->getSafeSpawn());
                break;
            case "gapple":
                $player->teleport(Server::getInstance()->getLevelByName(self::GAPPLE_LEVEL)->getSafeSpawn());
                break;
            case "sumo":
                $player->teleport(Server::getInstance()->getLevelByName(self::SUMO_LEVEL)->getSafeSpawn());
                break;
        }
    }

    public static function remove(string $name)
    {
        self::$playerCount = self::$playerCount-1;
        self::$eliminatedCount = self::$eliminatedCount+1;

        unset(self::$players[$name]);
        self::$isEliminated[$name] = true;
    }

    public static function sendMessage($message)
    {
        foreach(self::$players as $playerName => $value)
        {
            $player = Server::getInstance()->getPlayer($playerName);

            if(!is_null($player))
            {
                $player->sendMessage($message);
            }
        }
    }

    public static function sendPopup($message)
    {
        foreach(self::$players as $playerName => $value)
        {
            $player = Server::getInstance()->getPlayer($playerName);

            if(!is_null($player))
            {
                $player->sendPopup($message);
            }
        }
    }
}