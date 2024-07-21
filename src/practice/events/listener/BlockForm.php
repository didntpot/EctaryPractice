<?php

namespace practice\events\listener;

use pocketmine\event\Listener;
use pocketmine\event\block\BlockFormEvent;

class BlockForm implements Listener
{
    public function onBlockForm(BlockFormEvent $event)
    {
        $block = $event->getBlock();

        if($block->getLevel()->getName() == "buhc")
        {
            $event->setCancelled();
        }
    }
}