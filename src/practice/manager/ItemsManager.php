<?php

namespace practice\manager;

use practice\items\{
    EnderPearl,
    SplashPotion,
    Rod,
    Fireworks
};
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;

class ItemsManager extends ItemFactory
{
    public static function initItems()
    {
        self::registerItem(new EnderPearl(), true);
        self::registerItem(new Rod, true);
        self::registerItem(new SplashPotion, true);
        self::registerItem(new Fireworks, true);
        Item::initCreativeItems();
    }
}