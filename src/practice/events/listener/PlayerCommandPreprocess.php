<?php

namespace practice\events\listener;

use practice\duels\DuelQueue;
use practice\duels\manager\DuelsManager;
use practice\manager\PlayerManager;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCommandPreprocessEvent;

class PlayerCommandPreprocess implements Listener
{
    public function onPlayerCommandPreprocess(PlayerCommandPreprocessEvent $event)
    {
        $player = $event->getPlayer();
        $message = $event->getMessage();

        if (strpos($message, "/") === 0) {

            if (DuelsManager::isInDuel($player->getName()))
            {
                if(!$player->hasPermission("staff.command"))
                {
                    $player->sendMessage("§c» You can't execute command while being in a duel.");
                    $event->setCancelled();
                }
                return;
            }elseif (DuelQueue::isInQueue($player->getName()))
            {
                $player->sendMessage("§c» You can't execute command while being in a queue.");
                $event->setCancelled();
                return;
            }

            if (isset(PlayerManager::$combat_time[$player->getName()])) {
                if (!$player->isOp()) {
                    $event->setCancelled();
                    $player->sendMessage("§c» You're currently in combat, wait " . PlayerManager::$combat_time[$player->getName()] . " second(s) until you can execute commands.");
                }
            }

            if($player->getLevel()->getName() == "kitroom")
            {
                $event->setCancelled();
                $player->sendMessage("§c» You can't execute commands in the kit room.");
            }
        }
    }
}