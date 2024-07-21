<?php

namespace practice\forms;

use practice\api\KitsAPI;
use practice\api\form\SimpleForm;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\level\Location;
use practice\events\listener\PlayerJoin;
use practice\manager\SQLManager;
use practice\scoreboard\KitRoomScoreboard;

class KitEditorForm
{
    public static function openForm(Player $player)
    {
        $form = new SimpleForm(function(Player $player, $data){
           if($data === null) return;

           switch($data)
           {
               case 0:
                   KitsAPI::addNodebuffKit($player);

                   $player->getArmorInventory()->clearAll();
                   KitsAPI::$isEditing[$player->getName()] = "NoDebuff";
                   $player->setImmobile(true);
                   self::tpToRoom($player);
                   break;
               case 1:
                   KitsAPI::addDebuffKit($player);

                   $player->getArmorInventory()->clearAll();
                   KitsAPI::$isEditing[$player->getName()] = "Debuff";
                   $player->setImmobile(true);
                   self::tpToRoom($player);
                   break;
               case 2:
                   KitsAPI::addBuildKit($player);

                   $player->getArmorInventory()->clearAll();
                   KitsAPI::$isEditing[$player->getName()] = "Build";
                   $player->setImmobile(true);
                   self::tpToRoom($player);
                   break;
               case 3:
                   KitsAPI::addBuildUHCKit($player);

                   $player->getArmorInventory()->clearAll();
                   KitsAPI::$isEditing[$player->getName()] = "BuildUHC";
                   $player->setImmobile(true);
                   self::tpToRoom($player);
                   break;
               case 4:
                   KitsAPI::addFinalUHCKit($player);

                   $player->getArmorInventory()->clearAll();
                   KitsAPI::$isEditing[$player->getName()] = "FinalUHC";
                   $player->setImmobile(true);
                   self::tpToRoom($player);
                   break;
               case 5:
                   KitsAPI::addCaveUHCKit($player);

                   $player->getArmorInventory()->clearAll();
                   KitsAPI::$isEditing[$player->getName()] = "CaveUHC";
                   $player->setImmobile(true);
                   self::tpToRoom($player);
                   break;
               case 6:
                   KitsAPI::addPitchoutKit($player);

                   $player->getArmorInventory()->clearAll();
                   KitsAPI::$isEditing[$player->getName()] = "PitchOut";
                   $player->setImmobile(true);
                   self::tpToRoom($player);
                   break;
               case 7:
                   KitsAPI::addHGKit($player);

                   $player->getArmorInventory()->clearAll();
                   KitsAPI::$isEditing[$player->getName()] = "HG";
                   $player->setImmobile(true);
                   self::tpToRoom($player);
                   break;
               case 8:
                   KitsAPI::addMLGRushKit($player);

                   $player->getArmorInventory()->clearAll();
                   KitsAPI::$isEditing[$player->getName()] = "MLG Rush";
                   $player->setImmobile(true);
                   self::tpToRoom($player);
                   break;
           }

        });
        $form->setTitle("Kits");
        $form->setContent("Select a kit to edit it :");
        $form->addButton("Nodebuff", 0, "textures/items/potion_bottle_splash_heal");
        $form->addButton("Debuff", 0, "textures/items/potion_bottle_splash_poison");
        $form->addButton("Build", 0, "textures/items/iron_pickaxe");
        $form->addButton("BuildUHC", 0, "textures/items/bucket_lava");
        $form->addButton("FinalUHC", 0, "textures/items/arrow");
        $form->addButton("CaveUHC", 0, "textures/items/string");
        $form->addButton("PitchOut", 0, "textures/items/feather");
        $form->addButton("HG", 0, "textures/items/stone_pickaxe");
        $form->addButton("MLG Rush", 0, "textures/items/bed_red");
        $form->addButton("« Exit");
        $form->sendToPlayer($player);
    }

    public static function tpToRoom(Player $player)
    {
        $level = Server::getInstance()->getLevelByName("kitroom");

        if(!is_null($level))
        {
            $player->teleport(new Location(255.5000, 9, 253.5000, 0, 0, $level));
            PlayerJoin::$scoreboard[$player->getName()] = new KitRoomScoreboard($player);
            PlayerJoin::$scoreboard[$player->getName()]->sendRemoveObjectivePacket();
            KitRoomScoreboard::createLines($player);
        }
    }
}