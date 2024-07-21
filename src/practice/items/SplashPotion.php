<?php

namespace practice\items;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\item\SplashPotion as PMSplashPotion;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AnimatePacket;

class SplashPotion extends PMSplashPotion
{

    public function __construct(int $meta = 0)
    {
        parent::__construct(self::SPLASH_POTION, $meta, "Splash Potion");
    }

    public function getMaxStackSize(): int
    {
        return 1;
    }

    public function getProjectileEntityType(): string
    {
        return "ThrownPotion";
    }

    public function getThrowForce(): float
    {
        return 0.4; #0.6 base
    }

    protected function addExtraTags(CompoundTag $tag): void
    {
        $tag->setShort("PotionId", $this->meta);
    }
}