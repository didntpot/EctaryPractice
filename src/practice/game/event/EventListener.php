<?php

namespace practice\game\event;

use practice\Main;
use pocketmine\event\player\{
    PlayerCommandPreprocessEvent,
    PlayerQuitEvent
};
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\{event\Listener, Server, Player};
use pocketmine\utils\Config;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\level\Position;

class EventListener implements Listener
{
    public function onEntityLevelChange(PlayerCommandPreprocessEvent $event)
    {
        $player = $event->getPlayer();
        if ($player->getLevel()->getName() === "NodebuffE" or $player->getLevel()->getName() === "GappleE" or $player->getLevel()->getName() === "SumoE") {
            if (!$player->hasPermission("staff.command")) {
                if (strpos($event->getMessage(), "/") === 0) {
                    $player->sendMessage("§c» You can't execute a command while being in a match.");
                    $event->setCancelled();
                }
            }
        }
    }

    public function onQuit(PlayerQuitEvent $event)
    {
        $player = $event->getPlayer();
        if (isset(Manager::$is_ingame[$player->getName()])) {
            $config = new Config(Main::getInstance()->getDataFolder() . "event.json", Config::JSON);
            Manager::removeFromGame($player->getName());
            $config->remove($player->getName());
            $config->save();
        }
    }

    public function onEntityDamageByEntity(EntityDamageByEntityEvent $event)
    {
        $player = $event->getEntity();
        $block = $player->getLevel()->getBlock($player->floor()->subtract(0, 1));
        $block1 = $player->getLevel()->getBlock($player->floor()->subtract(0, 2));
        if ($player instanceof Player) {
            if ($player->getLevel()->getName() === "NodebuffE" or $player->getLevel()->getName() === "GappleE" or $player->getLevel()->getName() === "SumoE") {
                if ($block->getId() === 236 or $block1->getId() === 236) {
                }else{
                    $event->setCancelled();
                }
            }
        }
    }

    public function onEntityDamage(EntityDamageEvent $event)
    {
        $worlds = ["NodebuffE", "GappleE", "SumoE"];

        $player = $event->getEntity();

        if ($event->getFinalDamage() >= $player->getHealth() && $event->getCause() !== EntityDamageEvent::CAUSE_FALL) {
            foreach ($worlds as $world) {
                if ($player->getLevel()->getName() === $world) {
                    $event->setCancelled();
                    switch (Manager::$event_mode) {
                        case "nodebuff":
                            $player->teleport(new Position(500, -1, 284, Server::getInstance()->getLevelByName("NodebuffE")));
                            break;
                        case "gapple":
                            $player->teleport(new Position(500, -1, 234, Server::getInstance()->getLevelByName("GappleE")));
                            break;
                        case "sumo":
                            $player->teleport(new Position(500, -1, 265, Server::getInstance()->getLevelByName("SumoE")));
                            break;
                        default:
                            break;
                    }
                }
            }
        }
    }
}