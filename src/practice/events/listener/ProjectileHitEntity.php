<?php

namespace practice\events\listener;

use pocketmine\event\Listener;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\entity\projectile\Arrow;
use pocketmine\Player;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\entity\projectile\EnderPearl;
use practice\api\SoundAPI;
use practice\api\PlayerDataAPI;

class ProjectileHitEntity implements Listener
{

    public function onProjectileHitEntity(ProjectileHitEntityEvent $event)
    {
        $entity = $event->getEntity();
        $entityHit = $event->getEntityHit();
        $owner = $entity->getOwningEntity();

        if($owner instanceof Player and $entity instanceof Arrow)
        {
            switch(PlayerDataAPI::getSetting($owner->getName(), "bow_hit_sound"))
            {
                case "orb":
                    SoundAPI::playSound($owner, "random.orb");
                    break;
                case "anvil":
                    SoundAPI::playSound($owner, "random.anvil_land");
                    break;
                case "bell":
                    SoundAPI::playSound($owner, "note.bell");
                    break;
            }

            if($entityHit instanceof Player)
            {
                $owner->sendMessage("§e{$entityHit->getName()} is now at §b".round($entityHit->getHealth(), 1)."§e HP.");
            }
        }

        if($entity instanceof EnderPearl)
        {
            if($event instanceof EntityDamageByEntityEvent) $event->setKnockback(0);
        }
    }
}