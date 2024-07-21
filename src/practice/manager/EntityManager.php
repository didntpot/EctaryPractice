<?php

namespace practice\manager;

use practice\entity\{
    SplashPotion,
    Hook,
    EnderPearl,
    FireworksRocket
};
use pocketmine\entity\Entity;
use pocketmine\Player;

class EntityManager extends Entity
{
    public static function init(): void
    {
        self::registerEntity(SplashPotion::class, true, ['ThrownPotion', 'minecraft:potion', 'thrownpotion']);
        self::registerEntity(Hook::class, true, ['FishingHook', 'minecraft:fishinghook']);
        self::registerEntity(FireworksRocket::class, true, ["FireworksRocket", "minecraft:fireworks_rocket"]);
    }

    public static array $fishing = [];

    public static function getFishingHook($player): ?Hook
    {
        return self::$fishing[$player->getName()] ?? null;
    }

    public static function setFishingHook(?Hook $fish, $player)
    {
        if (!is_null($player)) {
            self::$fishing[$player->getName()] = $fish;
        }
    }
}