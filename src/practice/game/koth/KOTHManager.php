<?php

namespace practice\game\koth;

use pocketmine\Server;

class KOTHManager
{
    public static $captureTime = 0;
    public static $capturingPlayer = null;

    public static function resetAll()
    {
        self::$captureTime = 0;
        self::$capturingPlayer = null;
    }

    public static function resetTimers()
    {
        self::$captureTime = 0;
    }

    public static function sendMessage($message)
    {
        foreach(Server::getInstance()->getLevelByName("koth")->getPlayers() as $player)
        {
            $player->sendMessage($message);
        }
    }
}