<?php

namespace practice\events\listener;

use practice\api\PlayerDataAPI;
use practice\manager\PlayerManager;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;

class PlayerMove implements Listener
{
    public function onMove(PlayerMoveEvent $event)
    {
        $player = $event->getPlayer();

        if (isset(PlayerManager::$frozen[$player->getName()]) && PlayerManager::$frozen[$player->getName()] === true) {
            $event->setCancelled();
            $player->sendPopup("§cYou're currently frozen!");
        }
    }
}