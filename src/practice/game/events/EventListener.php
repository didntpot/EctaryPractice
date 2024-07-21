<?php

namespace practice\game\events;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\level\Position;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\event\entity\EntityDamageByEntityEvent;

class EventListener implements Listener
{
    public function onCommandPreprocess(PlayerCommandPreprocessEvent $event)
    {
        $player = $event->getPlayer();

        if(isset(EventManager::$players[$player->getName()]))
        {
            if (!$player->hasPermission("staff.command"))
            {
                if (strpos($event->getMessage(), "/") === 0)
                {
                    $player->sendMessage("§c» You can't execute a command while being in a event.");
                    $event->setCancelled();
                }
            }
        }
    }

    public function onEntityDamageByEntity(EntityDamageByEntityEvent $event)
    {
        $player = $event->getEntity();
        $block = $player->getLevel()->getBlock($player->floor()->subtract(0, 1));
        $block1 = $player->getLevel()->getBlock($player->floor()->subtract(0, 2));
        if ($player instanceof Player) {
            if ($player->getLevel()->getName() === "NodebuffE" or $player->getLevel()->getName() === "GappleE" or $player->getLevel()->getName() === "SumoE") {
                if($block->getId() === 98 or $block1->getId() === 98)
                {
                    $event->setCancelled();
                }
            }
        }
    }

    public function onQuit(PlayerQuitEvent $event)
    {
        $player = $event->getPlayer();

        if(isset(EventManager::$players[$player->getName()]))
        {
            EventManager::remove($player->getName());
        }
    }

    public function onEntityDamage(EntityDamageEvent $event)
    {
        $worlds = ["NodebuffE", "GappleE", "SumoE"];

        $player = $event->getEntity();

        if($event->getFinalDamage() >= $player->getHealth() && $event->getCause() !== EntityDamageEvent::CAUSE_FALL)
        {
            foreach($worlds as $world)
            {
                if($player->getLevel()->getName() === $world)
                {
                    $event->setCancelled();

                    switch(EventManager::$eventType)
                    {
                        case "nodebuff":
                            $player->teleport(new Position(500, 10, 284, Server::getInstance()->getLevelByName("NodebuffE")));
                            break;
                        case "gapple":
                            $player->teleport(new Position(500, 10, 234, Server::getInstance()->getLevelByName("GappleE")));
                            break;
                        case "sumo":
                            $player->teleport(new Position(500, 10, 265, Server::getInstance()->getLevelByName("SumoE")));
                            break;
                    }
                }
            }
        }
    }
}