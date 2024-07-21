<?php

namespace practice\events\listener;

use pocketmine\event\Listener;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\Player;
use pocketmine\Server;
use practice\manager\PlayerManager;

class EntityLevelChange implements Listener
{
    public function onEntityLevelChange(EntityLevelChangeEvent $event)
    {
        $world = $event->getTarget();
        $player = $event->getEntity();

        if ($event->getEntity() instanceof Player)
        {
            if(isset(PlayerManager::$boxingHits[$player->getName()]))
            {
                PlayerManager::$boxingHits[$player->getName()] = 0;
            }

            if(isset(PlayerManager::$playerPoints[$player->getName()]))
            {
                PlayerManager::$playerPoints[$player->getName()] = 0;
            }

            if(isset(PlayerManager::$playerTeam[$player->getName()]))
            {
                unset(PlayerManager::$playerTeam[$player->getName()]);
            }

            if(isset(PlayerManager::$combo[$player->getName()]))
            {
                PlayerManager::$combo[$player->getName()] = 0;
            }

            if(isset(PlayerManager::$reach[$player->getName()]))
            {
                PlayerManager::$reach[$player->getName()] = 0;
            }

            if($player instanceof Player)
            {
                if($world === Server::getInstance()->getLevelByName("spawn"))
                {
                    if($player->hasPermission("spawn.fly") or $player->isOp())
                    {
                    }
                }else{
                    if($player->hasPermission("staff.command")) return;
                    $player->setFlying(false);
                    $player->setAllowFlight(false);
                }
            }
        }
    }
}