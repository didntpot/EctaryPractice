<?php

namespace practice\events\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\Server;

class PlayerRespawn implements Listener
{
    public function onRespawn(PlayerRespawnEvent $event)
    {
        $player = $event->getPlayer();

        $player->teleport(Server::getInstance()->getLevelByName("spawn")->getSafeSpawn());
    }
}