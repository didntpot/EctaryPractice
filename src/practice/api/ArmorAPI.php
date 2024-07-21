<?php

namespace practice\api;

use pocketmine\item\Item;
use pocketmine\Player;

class ArmorAPI
{
    public const
        HELMET = [
        Item::LEATHER_HELMET,
        Item::CHAIN_HELMET,
        Item::IRON_HELMET,
        Item::GOLD_HELMET,
        Item::DIAMOND_HELMET,
    ],
        CHESTPLATE = [
        Item::LEATHER_CHESTPLATE,
        Item::CHAIN_CHESTPLATE,
        Item::IRON_CHESTPLATE,
        Item::GOLD_CHESTPLATE,
        Item::DIAMOND_CHESTPLATE,
        Item::ELYTRA,
    ],
        LEGGINGS = [
        Item::LEATHER_LEGGINGS,
        Item::CHAIN_LEGGINGS,
        Item::IRON_LEGGINGS,
        Item::GOLD_LEGGINGS,
        Item::DIAMOND_LEGGINGS,
    ],
        BOOTS = [
        Item::LEATHER_BOOTS,
        Item::CHAIN_BOOTS,
        Item::IRON_BOOTS,
        Item::GOLD_BOOTS,
        Item::DIAMOND_BOOTS,
    ];

    public static function setArmorByType(Item $armor, Player $player): void
    {
        $id = $armor->getId();

        if (in_array($id, self::HELMET, true)) {
            $copy = $player->getArmorInventory()->getHelmet();
            $set = $player->getArmorInventory()->setHelmet($armor);
        } elseif (in_array($id, self::CHESTPLATE, true)) {
            $copy = $player->getArmorInventory()->getChestplate();
            $set = $player->getArmorInventory()->setChestplate($armor);
        } elseif (in_array($id, self::LEGGINGS, true)) {
            $copy = $player->getArmorInventory()->getLeggings();
            $set = $player->getArmorInventory()->setLeggings($armor);
        } elseif (in_array($id, self::BOOTS, true)) {
            $copy = $player->getArmorInventory()->getBoots();
            $set = $player->getArmorInventory()->setBoots($armor);
        }
        if (isset($set) and $set) {
            /** @var Item $copy */
            $player->getInventory()->setItemInHand($copy);
        }
    }
}