<?php

namespace practice\entity;

use practice\api\PlayerDataAPI;
use practice\manager\PlayerManager;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\entity\projectile\SplashPotion as PMSplashPotion;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Living;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\item\Potion;
use pocketmine\entity\Entity;
use pocketmine\level\Level;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\utils\Color;
use pocketmine\entity\projectile\Throwable;
use pocketmine\Player;
use practice\duels\manager\DuelsManager;

class SplashPotion extends Throwable
{

    public const NETWORK_ID = self::SPLASH_POTION;

    protected $gravity = 0.05;
    protected $drag = 0.01;

    protected function initEntity(): void
    {
        parent::initEntity();

        $this->setPotionId($this->namedtag->getShort("PotionId", 0));
    }

    public function saveNBT(): void
    {
        parent::saveNBT();
        $this->namedtag->setShort("PotionId", $this->getPotionId());
    }

    public function getResultDamage(): int
    {
        return -1; //no damage
    }

    protected function onHit(ProjectileHitEvent $event): void
    {
        $effects = $this->getPotionEffects();
        $owner = $this->getOwningEntity();
        $hasEffects = true;
        $color = "default";
        if (count($effects) === 0) {
            $colors = [new Color(0x38, 0x5d, 0xc6)];
            $hasEffects = false;
        } else {
            //$colors=[new Color(0xf8, 0x24, 0x23)]; DEFAULT RED
            if ($owner instanceof Player && $color = PlayerDataAPI::getSetting($owner->getName(), "potions_colour")) ;
            switch ($color) {
                case "default":
                    $colors = [new Color(255, 0, 0)];
                    break;
                case "pink":
                    $colors = [new Color(250, 10, 226)];
                    break;
                case "purple":
                    $colors = [new Color(147, 4, 255)];
                    break;
                case "blue":
                    $colors = [new Color(2, 2, 255)];
                    break;
                case "cyan":
                    $colors = [new Color(4, 248, 255)];
                    break;
                case "green":
                    $colors = [new Color(4, 255, 55)];
                    break;
                case "yellow":
                    $colors = [new Color(248, 255, 0)];
                    break;
                case "orange":
                    $colors = [new Color(255, 128, 0)];
                    break;
                case "white":
                    $colors = [new Color(255, 255, 255)];
                    break;
                case "grey":
                    $colors = [new Color(150, 150, 150)];
                    break;
                case "black":
                    $colors = [new Color(0, 0, 0)];
                    break;
                default:
                    $colors = [new Color(255, 0, 0)];
                    break;
            }
            $hasEffects = true;
        }

        $this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_PARTICLE_SPLASH, Color::mix(...$colors)->toARGB());
        $this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_GLASS);

        if ($hasEffects) {
            if (!$this->willLinger()) {
                foreach ($this->level->getNearbyEntities($this->boundingBox->expandedCopy(4.125, 2.125, 4.125), $this) as $entity) {
                    if ($entity instanceof Living and $entity->isAlive()) {
                        $distanceSquared = $entity->add(0, $entity->getEyeHeight(), 0)->distanceSquared($this);
                        if ($distanceSquared > 24) { //1 block = 4 === 6 ##BASE 24
                            continue;
                        }

                        $distanceMultiplier = 1.2 - (sqrt($distanceSquared) / 6); ### BASE 1.2
                        if ($event instanceof ProjectileHitEntityEvent and $entity === $event->getEntityHit()) {
                            $distanceMultiplier = 1.0;
                        }

                        foreach ($this->getPotionEffects() as $effect) {
                            //getPotionEffects() is used to get COPIES to avoid accidentally modifying the same effect instance already applied to another entity

                            if (!$effect->getType()->isInstantEffect()) {
                                $newDuration = (int)round($effect->getDuration() * 0.95 * $distanceMultiplier);
                                if ($newDuration < 30) {
                                    continue;
                                }
                                $effect->setDuration($newDuration);

                                $entity->addEffect($effect);
                            } else {
                                $effect->getType()->applyEffect($entity, $effect, $distanceMultiplier, $this, $this->getOwningEntity());
                            }
                        }
                    }
                }
            } else {
                //TODO: lingering potions
            }
        } elseif ($event instanceof ProjectileHitBlockEvent and $this->getPotionId() === Potion::WATER) {
            $blockIn = $event->getBlockHit()->getSide($event->getRayTraceResult()->getHitFace());

            if ($blockIn->getId() === Block::FIRE) {
                $this->level->setBlock($blockIn, BlockFactory::get(Block::AIR));
            }
            foreach ($blockIn->getHorizontalSides() as $horizontalSide) {
                if ($horizontalSide->getId() === Block::FIRE) {
                    $this->level->setBlock($horizontalSide, BlockFactory::get(Block::AIR));
                }
            }
        }
    }

    /**
     * Returns the meta value of the potion item that this splash potion corresponds to. This decides what effects will be applied to the entity when it collides with its target.
     */
    public function getPotionId(): int
    {
        return $this->propertyManager->getShort(self::DATA_POTION_AUX_VALUE) ?? 0;
    }

    public function setPotionId(int $id): void
    {
        $this->propertyManager->setShort(self::DATA_POTION_AUX_VALUE, $id);
    }

    /**
     * Returns whether this splash potion will create an area-effect cloud when it lands.
     */
    public function willLinger(): bool
    {
        return $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_LINGER);
    }

    /**
     * Sets whether this splash potion will create an area-effect-cloud when it lands.
     */
    public function setLinger(bool $value = true): void
    {
        $this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_LINGER, $value);
    }

    /**
     * @return EffectInstance[]
     */
    public function getPotionEffects(): array
    {
        return Potion::getPotionEffectsById($this->getPotionId());
    }
}