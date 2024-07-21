<?php


namespace practice\events\listener;


use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use practice\manager\PlayerManager;

class PlayerPreprocessEvent implements Listener
{
    public function onCommand(PlayerCommandPreprocessEvent $event)
    {
        $player = $event->getPlayer();
        if (PlayerManager::isSync($player->getName())) $event->setCancelled();
    }
}