<?php

namespace practice\manager;

use pocketmine\Player;
use pocketmine\entity\Entity;
use pocketmine\level\particle\AngryVillagerParticle;
use pocketmine\level\particle\DustParticle;
use pocketmine\level\particle\EnchantmentTableParticle;
use pocketmine\level\particle\FlameParticle;
use pocketmine\level\particle\HappyVillagerParticle;
use pocketmine\level\particle\HeartParticle;
use pocketmine\level\particle\LavaDripParticle;
use pocketmine\level\particle\LavaParticle;
use pocketmine\level\particle\PortalParticle;
use pocketmine\level\particle\SmokeParticle;
use pocketmine\level\particle\WaterDripParticle;
use pocketmine\level\particle\ExplodeParticle;
use pocketmine\level\particle\WaterParticle;

class ParticleManager
{
    public $particles =
        [
            1 => "Angry Villager", 2 => "Enchantment", 3 => "Explode",
            4 => "Happy Villager", 5 => "Heart", 6 => "Flame",
            7 => "Lava", 8 => "Lava Drip", 9 => "Portal",
            10 => "Rainbow Dust", 11 => "Smoke", 12 => "Water",
            13 => "Water Drip"
        ];

    public function spawnParticle(Player $player, Entity $entity)
    {
        switch ($this->getParticle($player->getName()))
        {
            case 1:
                $entity->getLevel()->addParticle(new AngryVillagerParticle($entity->asVector3()));
                break;
            case 2:
                $entity->getLevel()->addParticle(new EnchantmentTableParticle($entity->asVector3()));
                break;
            case 3:
                $entity->getLevel()->addParticle(new ExplodeParticle($entity->asVector3()));
                break;
            case 4:
                $entity->getLevel()->addParticle(new HappyVillagerParticle($entity->asVector3()));
                break;
            case 5:
                $entity->getLevel()->addParticle(new HeartParticle($entity->asVector3()));
                break;
            case 6:
                $entity->getLevel()->addParticle(new FlameParticle($entity->asVector3()));
                break;
            case 7:
                $entity->getLevel()->addParticle(new LavaParticle($entity->asVector3()));
                break;
            case 8:
                $entity->getLevel()->addParticle(new LavaDripParticle($entity->asVector3()));
                break;
            case 9:
                $entity->getLevel()->addParticle(new PortalParticle($entity->asVector3()));
                break;
            case 10:
                $entity->getLevel()->addParticle(new DustParticle($entity->asVector3(), mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255)));
                break;
            case 11:
                $entity->getLevel()->addParticle(new SmokeParticle($entity->asVector3()));
                break;
            case 12:
                $entity->getLevel()->addParticle(new WaterParticle($entity->asVector3()));
                break;
            case 13:
                $entity->getLevel()->addParticle(new WaterDripParticle($entity->asVector3()));
        }
    }
}