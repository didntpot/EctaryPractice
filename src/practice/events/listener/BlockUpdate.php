<?php

namespace practice\events\listener;

use pocketmine\event\Listener;
use pocketmine\event\block\BlockUpdateEvent;

class BlockUpdate implements Listener
{
    public function onBlockUpdate(BlockUpdateEvent $event)
    {
        $block = $event->getBlock();

        if($block->getLevel()->getName() == "buhc")
        {
            $event->setCancelled();
        }
    }
}