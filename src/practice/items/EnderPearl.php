<?php

namespace practice\items;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\item\EnderPearl as PMEnderPearl;

class EnderPearl extends PMEnderPearl
{

    public function __construct(int $meta = 0)
    {
        parent::__construct(self::ENDER_PEARL, $meta, "Ender Pearl");
    }

    public function getMaxStackSize(): int
    {
        return 16;
    }

    public function getProjectileEntityType(): string
    {
        return "ThrownEnderpearl";
    }

    public function getThrowForce(): float
    {
        return 2.3;
    }

    public function getCooldownTicks(): int
    {
        return 15;
    }
}