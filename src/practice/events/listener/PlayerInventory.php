<?php


namespace practice\events\listener;


use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;

class PlayerInventory implements Listener
{
    const WORLD_NAME = ["spawn"];

    public function onMoveItem(InventoryTransactionEvent $event)
    {
        $player = $event->getTransaction()->getSource();
        if (in_array($player->getLevel()->getFolderName(), self::WORLD_NAME)) {
            if (!$player->isOp()) $event->setCancelled();
        }
    }
}