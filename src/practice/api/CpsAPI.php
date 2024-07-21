<?php

namespace practice\api;

use pocketmine\Player;

class CpsAPI
{
    public static array $clicksData = [];

    public static function initPlayerClickData(Player $player): void
    {
        self::$clicksData[$player->getLowerCaseName()] = [];
    }

    public static function addClick(Player $player): void
    {
        array_unshift(self::$clicksData[$player->getLowerCaseName()], microtime(true));
        if (count(self::$clicksData[$player->getLowerCaseName()]) >= 100) {
            array_pop(self::$clicksData[$player->getLowerCaseName()]);
        }
    }

    public static function getCps(Player $player, float $deltaTime = 1.0, int $roundPrecision = 1): float
    {
        if (!isset(self::$clicksData[$player->getLowerCaseName()]) || empty(self::$clicksData[$player->getLowerCaseName()])) return 0.0;
        $ct = microtime(true);
        return round(count(array_filter(self::$clicksData[$player->getLowerCaseName()], static function (float $t) use ($deltaTime, $ct): bool {
                return ($ct - $t) <= $deltaTime;
            })) / $deltaTime, $roundPrecision);
    }
}