<?php

namespace practice\events\listener;

use pocketmine\event\Listener;
use pocketmine\event\inventory\InventoryPickupItemEvent;

class InventoryPickupItem implements Listener
{
    public function onInventoryPickupItem(InventoryPickupItemEvent $event)
    {
        $item = $event->getItem();
        $id = $item->getId();

        if($id == 281)
        {
            $event->setCancelled();
        }
    }
}