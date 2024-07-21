<?php

namespace practice\events\listener;

use practice\api\KitsAPI;
use practice\duels\manager\DuelsManager;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\Listener;

class PlayerCraft implements Listener
{
    public function onCraft(CraftItemEvent $event)
    {
        $player = $event->getPlayer();

        if(isset(KitsAPI::$isEditing[$player->getName()])) return $event->setCancelled();

        if(!DuelsManager::isInDuel($player->getName()))
        {
            if(!$player->isOp())
            {
                $event->setCancelled();
            }
        }
    }
}