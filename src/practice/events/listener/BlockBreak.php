<?php

namespace practice\events\listener;

use pocketmine\Player;
use practice\duels\manager\DuelsManager;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;

class BlockBreak implements Listener
{

    CONST IDS = [133, 128, 65, 241, 139, 182, 181];

    public function onBreak(BlockBreakEvent $event)
    {
        $player = $event->getPlayer();
        $block = $event->getBlock();

        $event->setDrops([]);

        if($player->getLevel()->getName() == "spawn")
        {
            $event->setCancelled();
        }

        if($player->getLevel()->getName() == "kitroom")
        {
            $event->setCancelled();
        }

        if (self::isCanceled($player)) return;

        if ($player->getLevel()->getName() === "build") {
            if (in_array($block->getId(), self::IDS) or ($block->getId() == 24 and $block->getDamage() == 2))
            {
                $event->setCancelled();
            }else{
                $event->setDrops([]);
            }
        } else {
            if (!$player->isOp() && !DuelsManager::isInDuel($player->getName())) {
                $event->setCancelled();
            }
        }
    }

    public static function isCanceled(Player $player)
    {
        if (DuelsManager::isInDuel($player->getName())) return true;
    }
}