<?php

namespace practice\events\listener;

use practice\entity\SplashPotion;
use pocketmine\event\Listener;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\entity\projectile\Arrow;

use pocketmine\Player;

class ProjectileHitBlock implements Listener
{
    public function onProjectileHitBlock(ProjectileHitBlockEvent $event)
    {
        $entity = $event->getEntity();
        $player = $entity->getOwningEntity();
        if ($entity instanceof Arrow) $entity->flagForDespawn();
        if ($player instanceof Player && $entity instanceof SplashPotion) $player->setHealth($player->getHealth() + 0.5);

        if(!is_null($player)){
            if(!is_null($player->getLevel()))
            {
                if($player->getLevel()->getName() === "spawn") $entity->setOwningEntity(null);
            }
        }
    }
}