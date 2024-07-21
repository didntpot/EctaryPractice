<?php

namespace practice\manager;

use pocketmine\level\Location;
use pocketmine\Player;
use pocketmine\Server;
use practice\api\KitsAPI;
use practice\manager\{
    LevelManager,
    TimeManager,
    PlayerManager
};

class LevelManager
{
    const LEVEL_DEFAULT = "spawn";
    CONST LEVEL_NO_STATS = ["world"];

    public static function teleportSpawn(Player $player)
    {
        $level = Server::getInstance()->getLevelByName(self::LEVEL_DEFAULT);
        if (!is_null($level))
        {
            $player->teleport(new Location(0.5000, 18, 25.5000, 180.0, -2.0, $level));
        }else{
            $player->teleport(Server::getInstance()->getDefaultLevel()->getSafeSpawn());
        }
        if(isset(PlayerManager::$staff_mode[$player->getName()]) && PlayerManager::$staff_mode[$player->getName()] === true) return;
        KitsAPI::addLobbyKit($player);
    }
}