<?php

namespace practice\events\listener;

use practice\duels\manager\DuelsManager;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDropItemEvent;

class PlayerDropItem implements Listener
{
    public function onDropItem(PlayerDropItemEvent $event)
    {
        $player = $event->getPlayer();
        $item = $event->getItem();

        $duel = DuelsManager::getDuel($player->getName());

        if(DuelsManager::isInDuel($player->getName()))
        {
            $duel = DuelsManager::getDuel($player->getName());
            if (!is_null($duel))
            {
                if($duel->getKit() !== "skywars")
                {
                    $event->setCancelled(true);
                }elseif($item->getId() == 282)
                {
                    $event->setCancelled(false);
                }
            }
        }else{
            if($item->getId() == 282)
            {
                $event->setCancelled(false);
            }else{
                if(!$player->isOp())
                {
                    $event->setCancelled();
                }
            }
        }
    }
}