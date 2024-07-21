<?php

namespace practice\api;

use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\item\enchantment\{
    EnchantmentInstance,
    Enchantment
};
use pocketmine\entity\{
    Effect,
    EffectInstance
};
use practice\events\listener\PlayerJoin;
use practice\manager\KitsManager;
use practice\manager\PlayerManager;
use pocketmine\utils\Color;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use practice\scoreboard\SpawnScoreboard;

class KitsAPI
{
    public static $isEditing = [];

    public static $nodebuffKit = [];
    public static $debuffKit = [];
    public static $buildKit = [];
    public static $builduhcKit = [];
    public static $finaluhcKit = [];
    public static $caveuhcKit = [];
    public static $pitchoutKit = [];
    public static $hgKit = [];
    public static $mlgrushKit = [];

    /**
     * @param Player $player
     * @param string $type
     */
    public static function clear(Player $player, string $type)
    {
        if(!is_null($player))
        {
            switch ($type) {
                case "inv":
                    $player->getInventory()->clearAll();
                    break;
                case "armor":
                    $player->getArmorInventory()->clearAll();
                    break;
                case "all":
                    $player->getInventory()->clearAll();
                    $player->getArmorInventory()->clearAll();
                    $player->removeAllEffects();
                    $player->setHealth(20);
                    break;
                default:
                    $player->getInventory()->clearAll();
                    break;
            }
        }
    }

    /**
     * @param Player $player
     * @return null
     */
    public static function addLobbyKit(Player $player)
    {
        if (is_null($player)) return null;

        $items = [
            Item::get(276, 0, 1),
            Item::get(267, 0, 1),
            Item::get(272, 0, 1),
            Item::get(339, 0, 1),
            Item::get(421, 0, 1),
            Item::get(347, 0, 1),
            Item::get(388, 0, 1),
            Item::get(403, 0, 1)];

        $items[0]->setCustomName("§r§bUnranked Queue §7(Right Click)");
        $items[1]->setCustomName("§r§bRanked Queue §7(Right Click)");
        $items[2]->setCustomName("§r§bFFA §7(Right Click)");
        $items[3]->setCustomName("§r§bSpectate §7(Right Click)");
        $items[4]->setCustomName("§r§bParty §7(Right Click)");
        $items[5]->setCustomName("§r§bSettings §7(Right Click)");
        $items[6]->setCustomName("§r§bLeaderboards §7(Right Click)");
        $items[7]->setCustomName("§r§bEdit Kits §7(Right Click)");

        self::clear($player, "all");

        $player->getInventory()->setItem(0, $items[0]);
        $player->getInventory()->setItem(1, $items[1]);
        $player->getInventory()->setItem(2, $items[2]);
        $player->getInventory()->setItem(3, $items[3]);

        $player->getInventory()->setItem(5, $items[4]);
        $player->getInventory()->setItem(6, $items[5]);
        $player->getInventory()->setItem(7, $items[6]);
        $player->getInventory()->setItem(8, $items[7]);

        $player->extinguish();
        $player->setGamemode(2);

        if(isset(PlayerManager::$finished[$player->getName()]))
        {
            unset(PlayerManager::$finished[$player->getName()]);
        }

        unset(PlayerJoin::$scoreboard[$player->getName()]);
        PlayerJoin::$scoreboard[$player->getName()] = new SpawnScoreboard($player);
        PlayerJoin::$scoreboard[$player->getName()]->sendRemoveObjectivePacket();
        SpawnScoreboard::createLines($player);

    }

    public static function addStaffKit(Player $player)
    {
        $items = [
            Item::get(345, 0, 1),
            Item::get(79, 0, 1),
            Item::get(145, 0, 1)];

        $items2 = [
            Item::get(351, 1, 1),
            Item::get(351, 10, 1)];

        $items[0]->setCustomName("§r§bRandom teleportation");
        $items[1]->setCustomName("§r§bFreeze a player");
        $items[2]->setCustomName("§r§bPing Checker");

        $items2[0]->setCustomName("§r§bDisable Staff Chat");
        $items2[1]->setCustomName("§r§bEnable Staff Chat");

        self::clear($player, "all");

        foreach ($items as $item)
        {
            $player->getInventory()->addItem($item);
        }

        if(isset(PlayerManager::$staff_chat[$player->getName()]))
        {
            switch(PlayerManager::$staff_chat[$player->getName()])
            {
                case true:
                    $player->getInventory()->addItem($items2[0]);
                    break;
                case false:
                    $player->getInventory()->addItem($items2[1]);
                    break;
            }
        }

        $player->setGamemode(1);
    }

    public static function addHikabrainKit(Player $player, $reset = false)
    {
        $player->setGamemode(0);

        $enchantments = [
            new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 10 + 0),
            new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::PROTECTION), 2 + 0)];

        if(!is_null($player))
        {
            self::clear($player, "all");
        }

        $armors = [
            Item::get(298, 0, 1),
            Item::get(299, 0, 1),
            Item::get(300, 0, 1),
            Item::get(301, 0, 1)];


        if(isset(PlayerManager::$playerTeam[$player->getName()]))
        {
            switch(PlayerManager::$playerTeam[$player->getName()])
            {
                case "red":

                    $color = new Color(255, 0, 0);

                    foreach($armors as $armor)
                    {
                        $armor->setCustomColor($color);
                        $armor->addEnchantment($enchantments[0]);
                        $armor->addEnchantment($enchantments[1]);
                    }

                    $player->getArmorInventory()->setHelmet($armors[0]);
                    $player->getArmorInventory()->setChestplate($armors[1]);
                    $player->getArmorInventory()->setLeggings($armors[2]);
                    $player->getArmorInventory()->setBoots($armors[3]);

                    $items = [
                        Item::get(267, 0, 1),
                        Item::get(24, 0, 64),
                        Item::get(322, 0, 64),
                        Item::get(24, 0, 320),
                        Item::get(257, 0, 1)];

                    foreach($items as $item)
                    {
                        $player->getInventory()->addItem($item);
                    }

                    break;

                case "blue":

                    $color = new Color(0, 0, 255);

                    foreach($armors as $armor)
                    {
                        $armor->setCustomColor($color);
                        $armor->addEnchantment($enchantments[0]);
                        $armor->addEnchantment($enchantments[1]);
                    }

                    $player->getArmorInventory()->setHelmet($armors[0]);
                    $player->getArmorInventory()->setChestplate($armors[1]);
                    $player->getArmorInventory()->setLeggings($armors[2]);
                    $player->getArmorInventory()->setBoots($armors[3]);

                    $items = [
                        Item::get(267, 0, 1),
                        Item::get(24, 0, 64),
                        Item::get(322, 0, 64),
                        Item::get(24, 0, 320),
                        Item::get(257, 0, 1)];

                    foreach($items as $item)
                    {
                        $player->getInventory()->addItem($item);
                    }


                    break;

                default:
                    break;
            }
        }
    }

    public static function addTheBridgeKit(Player $player, $reset = false)
    {
        $player->setGamemode(0);
        $enchantments = [
            new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 10 + 0),
            new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::INFINITY), 1 + 0),
            new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::PROTECTION), 2 + 0)];

        if(!is_null($player))
        {
            self::clear($player, "all");
        }

        $armors = [
            Item::get(298, 0, 1),
            Item::get(299, 0, 1),
            Item::get(300, 0, 1),
            Item::get(301, 0, 1)];


        if(isset(PlayerManager::$playerTeam[$player->getName()]))
        {
            switch(PlayerManager::$playerTeam[$player->getName()])
            {
                case "red":

                    $color = new Color(255, 0, 0);

                    foreach($armors as $armor)
                    {
                        $armor->setCustomColor($color);
                        $armor->addEnchantment($enchantments[0]);
                        $armor->addEnchantment($enchantments[2]);
                    }

                    $player->getArmorInventory()->setHelmet($armors[0]);
                    $player->getArmorInventory()->setChestplate($armors[1]);
                    $player->getArmorInventory()->setLeggings($armors[2]);
                    $player->getArmorInventory()->setBoots($armors[3]);

                    $items = [
                        Item::get(267, 0, 1),
                        Item::get(261, 0, 1),
                        Item::get(35, 14, 64),
                        Item::get(322, 0, 3)->setCustomName("§r§eHeal Apple")];

                    $items[1]->addEnchantment($enchantments[1]);

                    $miscs = [
                        Item::get(359, 0, 1),
                        Item::get(262, 0, 1)];

                    foreach($items as $item)
                    {
                        $player->getInventory()->addItem($item);
                    }

                    $player->getInventory()->setItem(8, $miscs[0]);
                    $player->getInventory()->setItem(9, $miscs[1]);

                    break;

                case "blue":

                    $color = new Color(0, 0, 255);

                    foreach($armors as $armor)
                    {
                        $armor->setCustomColor($color);
                        $armor->addEnchantment($enchantments[0]);
                        $armor->addEnchantment($enchantments[2]);
                    }

                    $player->getArmorInventory()->setHelmet($armors[0]);
                    $player->getArmorInventory()->setChestplate($armors[1]);
                    $player->getArmorInventory()->setLeggings($armors[2]);
                    $player->getArmorInventory()->setBoots($armors[3]);

                    $items = [
                        Item::get(267, 0, 1),
                        Item::get(261, 0, 1),
                        Item::get(35, 11, 64),
                        Item::get(322, 0, 3)->setCustomName("§r§eHeal Apple")];

                    $items[1]->addEnchantment($enchantments[1]);

                    $miscs = [
                        Item::get(359, 0, 1),
                        Item::get(262, 0, 1)];

                    foreach($items as $item)
                    {
                        $player->getInventory()->addItem($item);
                    }

                    $player->getInventory()->setItem(8, $miscs[0]);
                    $player->getInventory()->setItem(9, $miscs[1]);

                    break;

                default:
                    break;
            }
        }
    }

    public static function addMLGRushKit(Player $player, $reset = false)
    {
        $effects = [new EffectInstance(Effect::getEffect(Effect::RESISTANCE), 50 * 999999, 50, false)];

        if (!is_null(KitsManager::getKits($player->getName(), "mlgrush")) and $reset == false)
        {
            $player->getInventory()->setContents(KitsManager::getKits($player->getName(), "mlgrush"));
        }else{

            $items = [
                Item::get(369, 0, 1),
                Item::get(24, 0, 32),
                Item::get(257, 0, 1)];

            if(!is_null($player))
            {
                self::clear($player, "all");
            }

            $items[0]->setCustomName("§r§bKnockback Stick");

            foreach($items as $item)
            {
                $player->getInventory()->addItem($item);
            }
        }

        $player->addEffect($effects[0]);

        $player->setGamemode(0);
    }

    public static function addSpleefKit(Player $player)
    {
        self::clear($player, "all");

        $items = [Item::get(277, 5, 1)];

        foreach($items as $item)
        {
            $player->getInventory()->addItem($item);
        }

        $player->setGamemode(0);
    }

    public static function addPartyKit(Player $player)
    {
        $items = [
            Item::get(276, 0, 1),
            Item::get(450, 0, 1),
            Item::get(339, 0, 1),
            Item::get(138, 0, 1),
            Item::get(331, 0, 1)];

        self::clear($player, "all");

        $items[0]->setCustomName("§r§bStart/Teleport");
        $items[1]->setCustomName("§r§bInvite");
        $items[2]->setCustomName("§r§bMembers");
        $items[3]->setCustomName("§r§bGamemode");
        $items[4]->setCustomName("§r§cLeave");

        $player->getInventory()->setItem(0, $items[0]);
        $player->getInventory()->setItem(1, $items[1]);
        $player->getInventory()->setItem(2, $items[2]);
        $player->getInventory()->setItem(3, $items[3]);
        $player->getInventory()->setItem(8, $items[4]);
    }

    public static function addNodebuffKit(Player $player, $reset = false, $speed2 = false)
    {
        $enchantments = [new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 10 + 0)];
        $effects = [
            new EffectInstance(Effect::getEffect(Effect::SPEED), 1 * 999999, 0, false),
            new EffectInstance(Effect::getEffect(Effect::SPEED), 2 * 999999, 1, false)];

        if (!is_null(KitsManager::getKits($player->getName(), "nodebuff")) and $reset == false)
        {
            $player->getInventory()->setContents(KitsManager::getKits($player->getName(), "nodebuff"));
        }else{
            $items = [
                Item::get(276, 0, 1),
                Item::get(368, 0, 16),
                Item::get(438, 22, 34)];



            self::clear($player, "all");

            $items[0]->addEnchantment($enchantments[0]);

            foreach ($items as $item) {
                $player->getInventory()->addItem($item);
            }
        }

        $armors = [
            Item::get(310, 0, 1),
            Item::get(311, 0, 1),
            Item::get(312, 0, 1),
            Item::get(313, 0, 1)];

        foreach ($armors as $armor) {
            $armor->addEnchantment($enchantments[0]);
        }

        $player->getArmorInventory()->setHelmet($armors[0]);
        $player->getArmorInventory()->setChestplate($armors[1]);
        $player->getArmorInventory()->setLeggings($armors[2]);
        $player->getArmorInventory()->setBoots($armors[3]);

        if($speed2 == false)
        {
            $player->addEffect($effects[0]);
        }else{
            $player->addEffect($effects[1]);
        }

        $player->setGamemode(0);
    }

    public static function addBoxingKit(Player $player)
    {
        self::clear($player, "all");

        $effects = [
            new EffectInstance(Effect::getEffect(Effect::RESISTANCE), 50 * 999999, 50, false),
            new EffectInstance(Effect::getEffect(Effect::SPEED), 1 * 999999, 0, false)];

        $player->getInventory()->addItem(Item::get(276, 0, 1));
        $player->addEffect($effects[0]);
        $player->addEffect($effects[1]);
    }

    public static function addNewDebuffKit(Player $player)
    {
        $armors = [
            Item::get(310, 0, 1),
            Item::get(311, 0, 1),
            Item::get(312, 0, 1),
            Item::get(313, 0, 1)];


        $enchantments = [new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 10 + 0)];
        $effects = [new EffectInstance(Effect::getEffect(Effect::SPEED), 1 * 999999, 0, false)];

        self::clear($player, "all");

        foreach ($armors as $armor) {
            $armor->addEnchantment($enchantments[0]);
        }

        $player->getArmorInventory()->setHelmet($armors[0]);
        $player->getArmorInventory()->setChestplate($armors[1]);
        $player->getArmorInventory()->setLeggings($armors[2]);
        $player->getArmorInventory()->setBoots($armors[3]);

        $contents = null;

        if(isset(self::$debuffKit[$player->getName()]))
        {
            $decode = unserialize(self::$debuffKit[$player->getName()]);

            $contents = $decode;
        }

        $inventory = $player->getInventory();

        $inventory->setContents($contents);

        $player->setGamemode(0);
        $player->addEffect($effects[0]);
    }

    public static function addNewHGKit(Player $player)
    {
        $armors = [
            Item::get(306, 0, 1),
            Item::get(307, 0, 1),
            Item::get(308, 0, 1),
            Item::get(309, 0, 1)];


        $enchantments = [new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 10 + 0)];

        self::clear($player, "all");

        foreach ($armors as $armor) {
            $armor->addEnchantment($enchantments[0]);
        }

        $player->getArmorInventory()->setHelmet($armors[0]);
        $player->getArmorInventory()->setChestplate($armors[1]);
        $player->getArmorInventory()->setLeggings($armors[2]);
        $player->getArmorInventory()->setBoots($armors[3]);

        $contents = null;

        if(isset(self::$hgKit[$player->getName()]))
        {
            $decode = unserialize(self::$hgKit[$player->getName()]);

            $contents = $decode;
        }

        $inventory = $player->getInventory();

        $inventory->setContents($contents);

        $player->setGamemode(0);
    }

    public static function addNewPitchOutKit(Player $player)
    {
        $effects = [new EffectInstance(Effect::getEffect(Effect::RESISTANCE), 50 * 999999, 50, false)];

        self::clear($player, "all");

        $contents = null;

        if(isset(self::$pitchoutKit[$player->getName()]))
        {
            $decode = unserialize(self::$pitchoutKit[$player->getName()]);

            $contents = $decode;
        }

        $inventory = $player->getInventory();

        $inventory->setContents($contents);

        $player->setGamemode(0);
        $player->addEffect($effects[0]);
    }


    public static function addNewNodebuffKit(Player $player)
    {
        $armors = [
            Item::get(310, 0, 1),
            Item::get(311, 0, 1),
            Item::get(312, 0, 1),
            Item::get(313, 0, 1)];


        $enchantments = [new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 10 + 0)];
        $effects = [new EffectInstance(Effect::getEffect(Effect::SPEED), 1 * 999999, 0, false)];

        self::clear($player, "all");

        foreach ($armors as $armor) {
            $armor->addEnchantment($enchantments[0]);
        }

        $player->getArmorInventory()->setHelmet($armors[0]);
        $player->getArmorInventory()->setChestplate($armors[1]);
        $player->getArmorInventory()->setLeggings($armors[2]);
        $player->getArmorInventory()->setBoots($armors[3]);

        $contents = null;

        if(isset(self::$nodebuffKit[$player->getName()]))
        {
            $decode = unserialize(self::$nodebuffKit[$player->getName()]);

            $contents = $decode;
        }

        $inventory = $player->getInventory();

        $inventory->setContents($contents);

        $player->setGamemode(0);
        $player->addEffect($effects[0]);
    }

    public static function addNewBuildKit(Player $player)
    {
        $armors = [
            Item::get(306, 0, 1),
            Item::get(315, 0, 1),
            Item::get(316, 0, 1),
            Item::get(309, 0, 1)];


        $enchantments = [new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 10 + 0)];

        self::clear($player, "all");

        foreach ($armors as $armor) {
            $armor->addEnchantment($enchantments[0]);
        }

        $player->getArmorInventory()->setHelmet($armors[0]);
        $player->getArmorInventory()->setChestplate($armors[1]);
        $player->getArmorInventory()->setLeggings($armors[2]);
        $player->getArmorInventory()->setBoots($armors[3]);

        $contents = null;

        if(isset(self::$buildKit[$player->getName()]))
        {
            $decode = unserialize(self::$buildKit[$player->getName()]);

            $contents = $decode;
        }

        $inventory = $player->getInventory();

        $inventory->setContents($contents);

        $player->setGamemode(0);
    }

    public static function addNewBuildUHCKit(Player $player)
    {
        $armors = [
            Item::get(310, 0, 1),
            Item::get(311, 0, 1),
            Item::get(312, 0, 1),
            Item::get(313, 0, 1)];


        $enchantments = [new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 10 + 0)];

        self::clear($player, "all");

        foreach ($armors as $armor) {
            $armor->addEnchantment($enchantments[0]);
        }

        $player->getArmorInventory()->setHelmet($armors[0]);
        $player->getArmorInventory()->setChestplate($armors[1]);
        $player->getArmorInventory()->setLeggings($armors[2]);
        $player->getArmorInventory()->setBoots($armors[3]);

        $contents = null;

        if(isset(self::$builduhcKit[$player->getName()]))
        {
            $decode = unserialize(self::$builduhcKit[$player->getName()]);

            $contents = $decode;
        }

        $inventory = $player->getInventory();

        $inventory->setContents($contents);

        $player->setGamemode(0);
    }

    public static function addNewFinalUHCKit(Player $player)
    {
        $enchantments = [
            new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 10 + 0),
            new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::PROTECTION), 1 + 0)];

        $armors1 = [
            Item::get(310, 0, 1),
            Item::get(311, 0, 1),
            Item::get(312, 0, 1),
            Item::get(313, 0, 1)];

        self::clear($player, "all");

        foreach ($armors1 as $armor) {
            $armor->addEnchantment($enchantments[0]);
            $armor->addEnchantment($enchantments[1]);
        }

        $player->getArmorInventory()->setHelmet($armors1[0]);
        $player->getArmorInventory()->setChestplate($armors1[1]);
        $player->getArmorInventory()->setLeggings($armors1[2]);
        $player->getArmorInventory()->setBoots($armors1[3]);

        $contents = null;

        if(isset(self::$finaluhcKit[$player->getName()]))
        {
            $decode = unserialize(self::$finaluhcKit[$player->getName()]);

            $contents = $decode;
        }

        $inventory = $player->getInventory();

        $inventory->setContents($contents);

        $player->setGamemode(0);
    }

    public static function addNewCaveUHCKit(Player $player)
    {
        $enchantments = [
            new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 10 + 0),
            new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::PROTECTION), 1 + 0)];

        $armors1 = [
            Item::get(310, 0, 1),
            Item::get(311, 0, 1),
            Item::get(312, 0, 1),
            Item::get(313, 0, 1)];

        self::clear($player, "all");

        foreach ($armors1 as $armor) {
            $armor->addEnchantment($enchantments[0]);
            $armor->addEnchantment($enchantments[1]);
        }

        $player->getArmorInventory()->setHelmet($armors1[0]);
        $player->getArmorInventory()->setChestplate($armors1[1]);
        $player->getArmorInventory()->setLeggings($armors1[2]);
        $player->getArmorInventory()->setBoots($armors1[3]);

        $contents = null;

        if(isset(self::$caveuhcKit[$player->getName()]))
        {
            $decode = unserialize(self::$caveuhcKit[$player->getName()]);

            $contents = $decode;
        }

        $inventory = $player->getInventory();

        $inventory->setContents($contents);

        $player->setGamemode(0);
    }

    public static function addFistKit(Player $player)
    {
        self::clear($player, "all");

        $player->setGamemode(2);

        $player->getInventory()->addItem(Item::get(364, 0, 1));
    }

    public static function addPitchOutKit(Player $player, $reset = false)
    {
        $effects = [new EffectInstance(Effect::getEffect(Effect::RESISTANCE), 50 * 999999, 50, false)];

        $enchantments = [
            new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::PUNCH), 2 + 0),
            new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::INFINITY), 1 + 0)];

        if (!is_null(KitsManager::getKits($player->getName(), "pitchout")) and $reset == false)
        {
            $player->getInventory()->setContents(KitsManager::getKits($player->getName(), "pitchout"));
        }else{
            $items = [
                Item::get(369, 0, 1),
                Item::get(261, 0, 1),
                Item::get(262, 0, 1),
                Item::get(288, 0, 1)];

            $items[0]->setCustomName("§r§bKnockback Stick");
            $items[3]->setCustomName("§r§bLeap");

            $items[1]->addEnchantment($enchantments[0]);
            $items[1]->addEnchantment($enchantments[1]);

            self::clear($player, "all");

            $player->getInventory()->setItem(0, $items[0]);
            $player->getInventory()->setItem(1, $items[1]);
            $player->getInventory()->setItem(7, $items[2]);
            $player->getInventory()->setItem(8, $items[3]);

        }

        $player->setGamemode(0);

        $player->addEffect($effects[0]);
    }

    public static function addKothKit(Player $player)
    {
        $enchantments = [
            new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 10 + 0),
            new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::PROTECTION), 4 + 0)];

        $armors = [
            Item::get(302, 0, 1),
            Item::get(303, 0, 1),
            Item::get(304, 0, 1),
            Item::get(305, 0, 1)];

        $items = [
            Item::get(267, 0, 1),
            Item::get(261, 0, 1),
            Item::get(322, 0, 2),
            Item::get(438, 22, 15),
            Item::get(262, 0, 5)];

        foreach($armors as $armor)
        {
            $armor->addEnchantment($enchantments[0]);
            $armor->addEnchantment($enchantments[1]);
        }

        $items[0]->addEnchantment($enchantments[0]);

        self::clear($player, "all");

        $player->getArmorInventory()->setHelmet($armors[0]);
        $player->getArmorInventory()->setChestplate($armors[1]);
        $player->getArmorInventory()->setLeggings($armors[2]);
        $player->getArmorInventory()->setBoots($armors[3]);

        foreach($items as $item)
        {
            $player->getInventory()->addItem($item);
        }

        $player->setGamemode(0);
    }

    public static function addBuildUHCKit(Player $player, $reset = false)
    {
        $enchantments = [new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 10 + 0)];

        if (!is_null(KitsManager::getKits($player->getName(), "builduhc")) and $reset == false)
        {
            $player->getInventory()->setContents(KitsManager::getKits($player->getName(), "builduhc"));

        }else{

            $items = [
                Item::get(276, 0, 1),
                Item::get(261, 0, 1),
                Item::get(346, 0, 1),
                Item::get(4, 0, 64),
                Item::get(325, 10, 1),
                Item::get(325, 8, 1)];

            $gapples = [
                Item::get(322, 0, 6),
                Item::get(322, 0, 3)];

            $miscs = [
                Item::get(278, 0, 1),
                Item::get(4, 0, 64),
                Item::get(325, 8, 2),
                Item::get(262, 0, 32)];

            self::clear($player, "all");


            $items[0]->addEnchantment($enchantments[0]);

            foreach($items as $item)
            {
                $player->getInventory()->addItem($item);
            }

            $gapples[1]->setCustomName("§r§eGolden Head");
            $player->getInventory()->setItem(6, $gapples[0]);
            $player->getInventory()->setItem(7, $gapples[1]);

            foreach($miscs as $misc)
            {
                $player->getInventory()->addItem($misc);
            }

        }

        $armors = [
            Item::get(310, 0, 1),
            Item::get(311, 0, 1),
            Item::get(312, 0, 1),
            Item::get(313, 0, 1)];

        foreach($armors as $armor)
        {
            $armor->addEnchantment($enchantments[0]);
        }

        $player->getArmorInventory()->setHelmet($armors[0]);
        $player->getArmorInventory()->setChestplate($armors[1]);
        $player->getArmorInventory()->setLeggings($armors[2]);
        $player->getArmorInventory()->setBoots($armors[3]);

        $player->setGamemode(0);
    }

    public static function addFinalUHCKit(Player $player, $reset = false)
    {
        $enchantments = [
            new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 10 + 0),
            new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::PROTECTION), 1 + 0)];

        if (!is_null(KitsManager::getKits($player->getName(), "finaluhc")) and $reset == false)
        {
            $player->getInventory()->setContents(KitsManager::getKits($player->getName(), "finaluhc"));

        }else{

            $items = [
                Item::get(276, 0, 1),
                Item::get(346, 0, 1),
                Item::get(325, 10, 1),
                Item::get(278, 0, 1),
                Item::get(4, 0, 64),
                Item::get(322, 0, 24),
                Item::get(322, 0, 4),
                Item::get(259, 0, 1),
                Item::get(325, 8, 1),
                Item::get(310, 0, 1),
                Item::get(311, 0, 1),
                Item::get(312, 0, 1),
                Item::get(313, 0, 1)];

            $misc = [
                Item::get(325, 10, 1),
                Item::get(325, 8, 2),
                Item::get(4, 0, 64)];

            $items[0]->addEnchantment($enchantments[0]);


            $items[9]->addEnchantment($enchantments[1]);
            $items[10]->addEnchantment($enchantments[1]);
            $items[11]->addEnchantment($enchantments[1]);
            $items[12]->addEnchantment($enchantments[1]);

            $items[9]->setDamage(150);
            $items[10]->setDamage(300);
            $items[11]->setDamage(200);
            $items[12]->setDamage(150);

            $items[7]->setDamage(55);
            $items[6]->setCustomName("§r§eGolden Head");

            self::clear($player, "all");

            foreach($items as $item)
            {
                $player->getInventory()->addItem($item);
            }

            foreach($misc as $miscs)
            {
                $player->getInventory()->addItem($miscs);
            }

        }


        $armors1 = [
            Item::get(310, 0, 1),
            Item::get(311, 0, 1),
            Item::get(312, 0, 1),
            Item::get(313, 0, 1)];

        foreach($armors1 as $armorOne)
        {
            $armorOne->addEnchantment($enchantments[1]);
        }

        $armors1[0]->setDamage(150);
        $armors1[1]->setDamage(300);
        $armors1[2]->setDamage(200);
        $armors1[3]->setDamage(150);


        $player->getArmorInventory()->setHelmet($armors1[0]);
        $player->getArmorInventory()->setChestplate($armors1[1]);
        $player->getArmorInventory()->setLeggings($armors1[2]);
        $player->getArmorInventory()->setBoots($armors1[3]);

        $player->setGamemode(0);

    }

    public static function addCaveUHCKit(Player $player, $reset = false)
    {

        $enchantments = [
            new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 10 + 0),
            new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::PROTECTION), 1 + 0)];

        if (!is_null(KitsManager::getKits($player->getName(), "caveuhc")) and $reset == false)
        {
            $player->getInventory()->setContents(KitsManager::getKits($player->getName(), "caveuhc"));

        }else{

            $items = [
                Item::get(276, 0, 1),
                Item::get(346, 0, 1),
                Item::get(325, 10, 1),
                Item::get(278, 0, 1),
                Item::get(4, 0, 64),
                Item::get(322, 0, 16),
                Item::get(322, 0, 1),
                Item::get(259, 0, 1),
                Item::get(325, 8, 1),
                Item::get(310, 0, 1),
                Item::get(311, 0, 1),
                Item::get(312, 0, 1),
                Item::get(313, 0, 1)];

            $misc = [
                Item::get(325, 10, 1),
                Item::get(325, 8, 2),
                Item::get(4, 0, 64)];

            $items[0]->addEnchantment($enchantments[0]);


            $items[9]->addEnchantment($enchantments[1]);
            $items[10]->addEnchantment($enchantments[1]);
            $items[11]->addEnchantment($enchantments[1]);
            $items[12]->addEnchantment($enchantments[1]);

            $items[9]->setDamage(150);
            $items[10]->setDamage(300);
            $items[11]->setDamage(200);
            $items[12]->setDamage(150);

            $items[7]->setDamage(55);
            $items[6]->setCustomName("§r§eGolden Head");

            self::clear($player, "all");

            foreach($items as $item)
            {
                $player->getInventory()->addItem($item);
            }


            foreach($misc as $miscs)
            {
                $player->getInventory()->addItem($miscs);
            }

        }

        $armors1 = [
            Item::get(310, 0, 1),
            Item::get(311, 0, 1),
            Item::get(312, 0, 1),
            Item::get(313, 0, 1)];

        $armors1[0]->setDamage(150);
        $armors1[1]->setDamage(300);
        $armors1[2]->setDamage(200);
        $armors1[3]->setDamage(150);

        foreach($armors1 as $armorOne)
        {
            $armorOne->addEnchantment($enchantments[1]);
        }

        $player->getArmorInventory()->setHelmet($armors1[0]);
        $player->getArmorInventory()->setChestplate($armors1[1]);
        $player->getArmorInventory()->setLeggings($armors1[2]);
        $player->getArmorInventory()->setBoots($armors1[3]);

        $player->setGamemode(0);

    }

    public static function addHGKit(Player $player, $reset = false)
    {
        $enchantments = [new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 10 + 0)];

        if (!is_null(KitsManager::getKits($player->getName(), "hg")) and $reset == false)
        {
            $player->getInventory()->setContents(KitsManager::getKits($player->getName(), "hg"));

        }else{

            $items = [
                Item::get(267, 0, 1),
                Item::get(325, 10, 1),
                Item::get(325, 8, 1),
                Item::get(282, 0, 4),
                Item::get(139, 0, 32),
                Item::get(4, 0, 32)];

            $miscs = [
                Item::get(306, 0, 1),
                Item::get(307, 0, 1),
                Item::get(308, 0, 1),
                Item::get(309, 0, 1),
                Item::get(281, 0, 64),
                Item::get(40, 0, 64),
                Item::get(39, 0, 64),
                Item::get(274, 0, 1),
                Item::get(275, 0, 1),
                Item::get(325, 10, 1),
                Item::get(282, 0, 17)];

            $items[0]->addEnchantment($enchantments[0]);

            self::clear($player, "all");


            foreach($items as $item)
            {
                $player->getInventory()->addItem($item);
            }

            foreach($miscs as $misc)
            {
                $player->getInventory()->addItem($misc);
            }

            $items[0]->addEnchantment($enchantments[0]);

        }

        $armors = [
            Item::get(306, 0, 1),
            Item::get(307, 0, 1),
            Item::get(308, 0, 1),
            Item::get(309, 0, 1)];

        $player->getArmorInventory()->setHelmet($armors[0]);
        $player->getArmorInventory()->setChestplate($armors[1]);
        $player->getArmorInventory()->setLeggings($armors[2]);
        $player->getArmorInventory()->setBoots($armors[3]);

        $player->setGamemode(0);
    }

    public static function addEventNodebuffKit(Player $player)
    {
        $armors = [
            Item::get(310, 0, 1),
            Item::get(311, 0, 1),
            Item::get(312, 0, 1),
            Item::get(313, 0, 1)];

        $items = [
            Item::get(276, 0, 1),
            Item::get(368, 0, 16),
            Item::get(438, 22, 7)];

        $effects = [new EffectInstance(Effect::getEffect(Effect::SPEED), 1 * 999999, 0, false)];

        $enchantments = [new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 10 + 0)];

        self::clear($player, "all");

        $items[0]->addEnchantment($enchantments[0]);

        foreach ($items as $item) {
            $player->getInventory()->addItem($item);
        }

        foreach ($armors as $armor) {
            $armor->addEnchantment($enchantments[0]);
        }

        $player->getArmorInventory()->setHelmet($armors[0]);
        $player->getArmorInventory()->setChestplate($armors[1]);
        $player->getArmorInventory()->setLeggings($armors[2]);
        $player->getArmorInventory()->setBoots($armors[3]);

        $player->addEffect($effects[0]);

        $player->setGamemode(0);
    }

    public static function addDebuffKit(Player $player, $reset = false)
    {
        $effects = [new EffectInstance(Effect::getEffect(Effect::SPEED), 1 * 999999, 0, false)];

        $enchantments = [new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 10 + 0)];

        if (!is_null(KitsManager::getKits($player->getName(), "debuff")) and $reset == false)
        {
            $player->getInventory()->setContents(KitsManager::getKits($player->getName(), "debuff"));

        }else{

            $items = [
                Item::get(276, 0, 1),
                Item::get(368, 0, 16),
                Item::get(438, 22, 28)];

            $misc = [
                Item::get(438, 25, 1), #poison x1
                Item::get(438, 17, 1), #slowness x1
                Item::get(438, 25, 2), #poison x2
                Item::get(438, 17, 2)]; #slowness x2


            self::clear($player, "all");

            $items[0]->addEnchantment($enchantments[0]);

            $player->getInventory()->setItem(2, $misc[0]);
            $player->getInventory()->setItem(3, $misc[1]);

            foreach ($items as $item) {
                $player->getInventory()->addItem($item);
            }

            $player->getInventory()->addItem($misc[2]);
            $player->getInventory()->addItem($misc[3]);



        }

        $armors = [
            Item::get(310, 0, 1),
            Item::get(311, 0, 1),
            Item::get(312, 0, 1),
            Item::get(313, 0, 1)];

        foreach ($armors as $armor) {
            $armor->addEnchantment($enchantments[0]);
        }

        $player->getArmorInventory()->setHelmet($armors[0]);
        $player->getArmorInventory()->setChestplate($armors[1]);
        $player->getArmorInventory()->setLeggings($armors[2]);
        $player->getArmorInventory()->setBoots($armors[3]);

        $player->setGamemode(0);
        $player->addEffect($effects[0]);

    }

    public static function addSumoKit(Player $player)
    {
        $effects = [new EffectInstance(Effect::getEffect(Effect::RESISTANCE), 50 * 999999, 50, false)];

        self::clear($player, "all");

        $player->addEffect($effects[0]);

        $player->setGamemode(0);
    }

    public static function addBuildKit(Player $player, $reset = false)
    {
        $enchantments = [new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 10 + 0)];

        if (!is_null(KitsManager::getKits($player->getName(), "build")) and $reset == false)
        {
            $player->getInventory()->setContents(KitsManager::getKits($player->getName(), "build"));

        }else{

            $items = [
                Item::get(283, 0, 1),
                Item::get(30, 0, 3),
                Item::get(322, 0, 2),
                Item::get(368, 0, 1)];

            $sandstone = Item::get(24, 0, 64);
            $black = Item::get(159, 15, 64);
            $brown = Item::get(159, 12, 64);
            $red = Item::get(159, 14, 64);
            $orange = Item::get(159, 1, 64);
            $yellow = Item::get(159, 4, 64);
            $lime = Item::get(159, 5, 64);
            $green = Item::get(159, 13, 64);
            $cyan = Item::get(159, 9, 64);
            $light_blue = Item::get(159, 3, 64);
            $blue = Item::get(159, 11, 64);
            $purple = Item::get(159, 10, 64);
            $magenta = Item::get(159, 2, 64);
            $pink = Item::get(159, 6, 64);

            $misc = [Item::get(257, 0, 1)];



            $items[0]->addEnchantment($enchantments[0]);
            $misc[0]->addEnchantment($enchantments[0]);

            self::clear($player, "all");

            switch (PlayerManager::getInformation($player->getName(), "block_select")) {
                case "sandstone":
                    $player->getInventory()->setItem(1, $sandstone);
                    break;
                case "black":
                    $player->getInventory()->setItem(1, $black);
                    break;
                case "brown":
                    $player->getInventory()->setItem(1, $brown);
                    break;
                case "red":
                    $player->getInventory()->setItem(1, $red);
                    break;
                case "orange":
                    $player->getInventory()->setItem(1, $orange);
                    break;
                case "yellow":
                    $player->getInventory()->setItem(1, $yellow);
                    break;
                case "lime":
                    $player->getInventory()->setItem(1, $lime);
                    break;
                case "green":
                    $player->getInventory()->setItem(1, $green);
                    break;
                case "cyan":
                    $player->getInventory()->setItem(1, $cyan);
                    break;
                case "light_blue":
                    $player->getInventory()->setItem(1, $light_blue);
                    break;
                case "blue":
                    $player->getInventory()->setItem(1, $blue);
                    break;
                case "purple":
                    $player->getInventory()->setItem(1, $purple);
                    break;
                case "magenta":
                    $player->getInventory()->setItem(1, $magenta);
                    break;
                case "pink":
                    $player->getInventory()->setItem(1, $pink);
                    break;
                default:
                    $player->getInventory()->setItem(1, $sandstone);
                    break;
            }

            foreach ($items as $item) {
                $player->getInventory()->addItem($item);
            }


            $player->getInventory()->setItem(8, $misc[0]);

        }


        $armors = [
            Item::get(306, 0, 1),
            Item::get(315, 0, 1),
            Item::get(316, 0, 1),
            Item::get(309, 0, 1)];


        foreach ($armors as $armor) {
            $armor->addEnchantment($enchantments[0]);
        }
        
        $player->getArmorInventory()->setHelmet($armors[0]);
        $player->getArmorInventory()->setChestplate($armors[1]);
        $player->getArmorInventory()->setLeggings($armors[2]);
        $player->getArmorInventory()->setBoots($armors[3]);

        $player->setGamemode(0);
    }

    public static function addComboKit(Player $player)
    {
        $armors = [
            Item::get(310, 0, 1),
            Item::get(311, 0, 1),
            Item::get(312, 0, 1),
            Item::get(313, 0, 1)];

        $items = [
            Item::get(276, 0, 1),
            Item::get(466, 0, 64),
            Item::get(310, 0, 1),
            Item::get(311, 0, 1),
            Item::get(312, 0, 1),
            Item::get(313, 0, 1)];

        $enchantments = [new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::PROTECTION), 10 + 0)];

        $effects = [
            new EffectInstance(Effect::getEffect(Effect::SPEED), 1 * 999999, 0, false),
            new EffectInstance(Effect::getEffect(Effect::STRENGTH), 1 * 999999, 0, false)];

        foreach ($armors as $armor) {
            $armor->addEnchantment($enchantments[0]);
        }

        $items[0]->addEnchantment($enchantments[0]);
        $items[2]->addEnchantment($enchantments[0]);
        $items[3]->addEnchantment($enchantments[0]);
        $items[4]->addEnchantment($enchantments[0]);
        $items[5]->addEnchantment($enchantments[0]);

        self::clear($player, "all");

        foreach ($effects as $effect) {
            $player->addEffect($effect);
        }

        $player->getArmorInventory()->setHelmet($armors[0]);
        $player->getArmorInventory()->setChestplate($armors[1]);
        $player->getArmorInventory()->setLeggings($armors[2]);
        $player->getArmorInventory()->setBoots($armors[3]);

        foreach ($items as $item) {
            $player->getInventory()->addItem($item);
        }

        $player->setGamemode(0);
    }

    public static function addGappleKit(Player $player)
    {
        $armors = [
            Item::get(310, 0, 1),
            Item::get(311, 0, 1),
            Item::get(312, 0, 1),
            Item::get(313, 0, 1)];

        $items = [
            Item::get(276, 0, 1),
            Item::get(322, 0, 5)];

        $enchantments = [new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 10 + 0)];

        foreach ($armors as $armor) {
            $armor->addEnchantment($enchantments[0]);
        }

        $items[0]->addEnchantment($enchantments[0]);

        self::clear($player, "all");

        $player->getArmorInventory()->setHelmet($armors[0]);
        $player->getArmorInventory()->setChestplate($armors[1]);
        $player->getArmorInventory()->setLeggings($armors[2]);
        $player->getArmorInventory()->setBoots($armors[3]);

        foreach ($items as $item) {
            $player->getInventory()->addItem($item);
        }

        $player->setGamemode(0);
    }

    public static function addArcherKit(Player $player)
    {
        $armors = [
            Item::get(298, 0, 1),
            Item::get(299, 0, 1),
            Item::get(300, 0, 1),
            Item::get(301, 0, 1)];

        $items = [
            Item::get(261, 0, 1),
            Item::get(322, 0, 8)];

        $misc = [Item::get(262, 0, 1)];

        $enchantments = [
            new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::INFINITY), 1 + 0),
            new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 10 + 0)];

        foreach ($armors as $armor) {
            $armor->addEnchantment($enchantments[1]);
        }

        $items[0]->addEnchantment($enchantments[0]);

        self::clear($player, "all");

        foreach ($items as $item) {
            $player->getInventory()->addItem($item);
        }

        $player->getInventory()->setItem(9, $misc[0]);

        $player->getArmorInventory()->setHelmet($armors[0]);
        $player->getArmorInventory()->setChestplate($armors[1]);
        $player->getArmorInventory()->setLeggings($armors[2]);
        $player->getArmorInventory()->setBoots($armors[3]);

        $player->setGamemode(0);
    }

    public static function addSoupRefillKit(Player $player)
    {
        $armors = [
            Item::get(306, 0, 1),
            Item::get(307, 0, 1),
            Item::get(308, 0, 1),
            Item::get(309, 0, 1)];

        $items = [
            Item::get(267, 0, 1),
            Item::get(282, 0, 35)];

        $enchantments = [new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 10 + 0)];
        $effects = [new EffectInstance(Effect::getEffect(Effect::SPEED), 1 * 999999, 0, false)];

        foreach ($armors as $armor) {
            $armor->addEnchantment($enchantments[0]);
        }

        $items[0]->addEnchantment($enchantments[0]);

        self::clear($player, "all");

        $player->addEffect($effects[0]);

        $player->getArmorInventory()->setHelmet($armors[0]);
        $player->getArmorInventory()->setChestplate($armors[1]);
        $player->getArmorInventory()->setLeggings($armors[2]);
        $player->getArmorInventory()->setBoots($armors[3]);

        foreach ($items as $item) {
            $player->getInventory()->addItem($item);
        }

        $player->setGamemode(0);
    }

    public static function addSoupKit(Player $player)
    {
        $armors = [
            Item::get(306, 0, 1),
            Item::get(307, 0, 1),
            Item::get(308, 0, 1),
            Item::get(309, 0, 1)];

        $items = [
            Item::get(267, 0, 1),
            Item::get(282, 0, 8)];

        $enchantments = [new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 10 + 0)];
        $effects = [new EffectInstance(Effect::getEffect(Effect::SPEED), 1 * 999999, 0, false)];

        foreach ($armors as $armor) {
            $armor->addEnchantment($enchantments[0]);
        }

        $items[0]->addEnchantment($enchantments[0]);

        self::clear($player, "all");

        $player->addEffect($effects[0]);

        $player->getArmorInventory()->setHelmet($armors[0]);
        $player->getArmorInventory()->setChestplate($armors[1]);
        $player->getArmorInventory()->setLeggings($armors[2]);
        $player->getArmorInventory()->setBoots($armors[3]);

        foreach ($items as $item) {
            $player->getInventory()->addItem($item);
        }

        $player->setGamemode(0);
    }

    public static function addHCFKit(Player $player)
    {
        $armors = [
            Item::get(310, 0, 1),
            Item::get(311, 0, 1),
            Item::get(312, 0, 1),
            Item::get(313, 0, 1)];

        $items = [
            Item::get(276, 0, 1),
            Item::get(368, 0, 16),
            Item::get(346, 0, 1),
            Item::get(322, 0, 8),
            Item::get(438, 22, 32)];

        $effects = [new EffectInstance(Effect::getEffect(Effect::SPEED), 1 * 999999, 0, false)];

        $enchantments = [new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 10 + 0)];

        self::clear($player, "all");

        $items[0]->addEnchantment($enchantments[0]);

        foreach ($items as $item) {
            $player->getInventory()->addItem($item);
        }

        foreach ($armors as $armor) {
            $armor->addEnchantment($enchantments[0]);
        }

        $player->getArmorInventory()->setHelmet($armors[0]);
        $player->getArmorInventory()->setChestplate($armors[1]);
        $player->getArmorInventory()->setLeggings($armors[2]);
        $player->getArmorInventory()->setBoots($armors[3]);

        $player->addEffect($effects[0]);

        $player->setGamemode(0);
    }

    public static function addClassicKit(Player $player)
    {
        $armors = [
            Item::get(310, 0, 1),
            Item::get(311, 0, 1),
            Item::get(312, 0, 1),
            Item::get(313, 0, 1)];

        $items = [
            Item::get(276, 0, 1),
            Item::get(346, 0, 1),
            Item::get(261, 0, 1),
            Item::get(322, 0, 8)];

        $misc = [Item::get(262, 0, 16)];

        $enchantments = [new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 10 + 0)];

        foreach ($armors as $armor) {
            $armor->addEnchantment($enchantments[0]);
        }

        $items[0]->addEnchantment($enchantments[0]);

        self::clear($player, "all");

        $player->getArmorInventory()->setHelmet($armors[0]);
        $player->getArmorInventory()->setChestplate($armors[1]);
        $player->getArmorInventory()->setLeggings($armors[2]);
        $player->getArmorInventory()->setBoots($armors[3]);

        foreach ($items as $item) {
            $player->getInventory()->addItem($item);
        }

        $player->getInventory()->setItem(9, $misc[0]);

        $player->setGamemode(0);
    }

    public static function addMCSGKit(Player $player)
    {
        $armors = [
            Item::get(306, 0, 1),
            Item::get(315, 0, 1),
            Item::get(304, 0, 1),
            Item::get(301, 0, 1)];

        $items = [
            Item::get(272, 0, 1),
            Item::get(346, 0, 1),
            Item::get(261, 0, 1),
            Item::get(322, 0, 1),
            Item::get(260, 0, 2),
            Item::get(297, 0, 2),
            Item::get(357, 0, 2),
            Item::get(396, 0, 1),
            Item::get(262, 0, 8)];

        $enchantments = [new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 10 + 0)];

        foreach ($armors as $armor) {
            $armor->addEnchantment($enchantments[0]);
        }

        $items[0]->addEnchantment($enchantments[0]);

        self::clear($player, "all");

        $player->getArmorInventory()->setHelmet($armors[0]);
        $player->getArmorInventory()->setChestplate($armors[1]);
        $player->getArmorInventory()->setLeggings($armors[2]);
        $player->getArmorInventory()->setBoots($armors[3]);

        foreach ($items as $item) {
            $player->getInventory()->addItem($item);
        }

        $player->setGamemode(0);
    }

    public static function addAxeKit(Player $player)
    {
        $armors = [
            Item::get(306, 0, 1),
            Item::get(307, 0, 1),
            Item::get(308, 0, 1),
            Item::get(309, 0, 1)];

        $items = [
            Item::get(258, 0, 1),
            Item::get(322, 0, 8),
            Item::get(438, 22, 7)];

        $enchantments = [new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 10 + 0)];
        $effects = [new EffectInstance(Effect::getEffect(Effect::SPEED), 1 * 999999, 0, false)];

        foreach ($armors as $armor) {
            $armor->addEnchantment($enchantments[0]);
        }

        $items[0]->addEnchantment($enchantments[0]);

        self::clear($player, "all");

        $player->addEffect($effects[0]);

        $player->getArmorInventory()->setHelmet($armors[0]);
        $player->getArmorInventory()->setChestplate($armors[1]);
        $player->getArmorInventory()->setLeggings($armors[2]);
        $player->getArmorInventory()->setBoots($armors[3]);

        foreach ($items as $item) {
            $player->getInventory()->addItem($item);
        }

        $player->setGamemode(0);
    }
}