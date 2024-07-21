<?php

namespace practice\duels\events;

use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use practice\duels\manager\DuelsManager;
use pocketmine\Server;
use pocketmine\level\Position;

class BlockPlace implements Listener
{
    public static $blocks = [];

    public function onPlaceAA(BlockPlaceEvent $event)
    {
        $player = $event->getPlayer();
        $block = $event->getBlock();

        if(!DuelsManager::isInDuel($player->getName()))
        {
            if(!$player->isOp() && !$player->getLevel()->getName() == "build")
            {
                $event->setCancelled();
            }
        }else{
            if(DuelsManager::getDuel($player->getName())->getKit() == "mlgrush")
            {
                $x = $block->x;
                $y = $block->y;
                $z = $block->z;
                if(!isset(self::$blocks[$player->getName()]))
                {
                    self::$blocks[$player->getName()] = [];
                }else{
                    $blocks = self::$blocks[$player->getName()];
                    $blocks[] = $x . ':' . $y . ':' . $z;
                    self::$blocks[$player->getName()] = $blocks;
                }

                $block1 = $block->getLevel()->getBlock($block->floor()->subtract(0, 1));
                $block2 = $block->getLevel()->getBlock($block->floor()->subtract(0, 1));

                if($block1->getId() == 241 && $block2->getId() == 241)
                {
                    $event->setCancelled();
                }
            }

            if(DuelsManager::getDuel($player->getName())->getKit() == "hikabrain")
            {
                $x = $block->x;
                $y = $block->y;
                $z = $block->z;
                if(!isset(self::$blocks[$player->getName()]))
                {
                    self::$blocks[$player->getName()] = [];
                }else{
                    $blocks = self::$blocks[$player->getName()];
                    $blocks[] = $x . ':' . $y . ':' . $z;
                    self::$blocks[$player->getName()] = $blocks;
                }

                $block1 = $block->getLevel()->getBlock($block->floor()->subtract(0, 1));
                $block2 = $block->getLevel()->getBlock($block->floor()->subtract(0, 1));

                if($block1->getId() == 241 && $block2->getId() == 241)
                {
                    $event->setCancelled();
                }
            }

            if(DuelsManager::getDuel($player->getName())->getKit() == "thebridge")
            {
                $obj1 = new Position(549, 60, 1802, $player->getLevel());
                $obj2 = new Position(549, 60, 1858, $player->getLevel());

                if($block->distance($obj1) < 9)
                {
                    $event->setCancelled();

                    $player->sendMessage("§c» You can't place blocks here.");
                }

                if($block->distance($obj2) < 9)
                {
                    $event->setCancelled();

                    $player->sendMessage("§c» You can't place blocks here.");
                }
            }
        }
    }
}