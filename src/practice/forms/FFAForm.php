<?php

namespace practice\forms;

use practice\api\form\SimpleForm;
use practice\api\KitsAPI;
use practice\events\listener\PlayerJoin;
use practice\provider\{
    WorkerProvider,
    WorldProvider
};
use practice\manager\PlayerManager;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\level\Position;
use practice\scoreboard\FFAScoreboard;

class FFAForm
{
    public static function openForm(Player $player)
    {
        $form = new SimpleForm(function (Player $player, $data) {
            if ($data === null) return;
            if($data !== 10)
            {
                self::addScb($player);
            }
            switch ($data) {
                case 0:
                    if (FFAForm::teleportPlayer($player, "nodebuff")) {
                        KitsAPI::addNodebuffKit($player);
                    }
                    break;
                case 1:
                    if (FFAForm::teleportPlayer($player, "gapple")) {
                        KitsAPI::addGappleKit($player);
                    }
                    break;
                case 2:
                    if (FFAForm::teleportPlayer($player, "buhc")) {
                        KitsAPI::addBuildUHCKit($player);
                    }
                    break;
                case 3:
                    if(WorldProvider::countWorld("sumo") > 14) return $player->sendMessage("§c» This arena is currently full.");
                    $level = Server::getInstance()->getLevelByName("sumo");
                    $rand_pos = [new Position(262, 3, 231, $level),
                        new Position(217, 3, 251, $level),
                        new Position(253, 3, 308, $level),
                        new Position(322, 4, 261, $level)];
                    if (!is_null($level))
                    {
                        $player->teleport($rand_pos[array_rand($rand_pos)]);
                        KitsAPI::addSumoKit($player);
                    }
                    break;
                case 4:
                    if (FFAForm::teleportPlayer($player, "soup")) {
                        KitsAPI::addSoupRefillKit($player);
                    }
                    break;
                case 5:
                    if(WorldProvider::countWorld("build") > 14) return $player->sendMessage("§c» This arena is currently full.");
                    $level = Server::getInstance()->getLevelByName("build");
                    $rand_pos = [new Position(225, 13, 288, $level),
                        new Position(251, 2, 288, $level),
                        new Position(222, 9, 261, $level),
                        new Position(221, 2, 230, $level),
                        new Position(253, 7, 217, $level),
                        new Position(279, 2, 250, $level)];
                    if (!is_null($level))
                    {
                        $player->teleport($rand_pos[array_rand($rand_pos)]);
                        KitsAPI::addBuildKit($player);
                    }
                    break;
                case 6:
                    if(WorldProvider::countWorld("pitchout") > 14) return $player->sendMessage("§c» This arena is currently full.");
                    if (FFAForm::teleportPlayer($player, "pitchout")) {
                        KitsAPI::addPitchoutKit($player);
                    }
                    break;
                case 7:
                    self::openFistForm($player);
                    break;
                case 8:
                    if(WorldProvider::countWorld("combo") > 14) return $player->sendMessage("§c» This arena is currently full.");
                    if (FFAForm::teleportPlayer($player, "combo")) {
                        KitsAPI::addComboKit($player);
                    }
                    break;
            }
        });
        $countFist = WorldProvider::countWorld("fist")+WorldProvider::countWorld("fist1");
        $form->setTitle("FreeForAll");
        $form->setContent("Select a ladder :");
        $form->addButton("NoDebuff\n" . WorldProvider::countWorld("nodebuff") . " playing", 0, "textures/items/potion_bottle_splash_heal");
        $form->addButton("Gapple\n" . WorldProvider::countWorld("gapple") . " playing", 0, "textures/items/apple_golden");
        $form->addButton("BuildUHC\n" . WorldProvider::countWorld("buhc") . " playing", 0, "textures/items/bucket_lava");
        $form->addButton("Sumo\n" . WorldProvider::countWorld("sumo") . " playing", 0, "textures/items/lead");
        $form->addButton("Soup\n" . WorldProvider::countWorld("soup") . " playing", 0, "textures/items/mushroom_stew");
        $form->addButton("Build\n" . WorldProvider::countWorld("build") . " playing", 0, "textures/items/iron_pickaxe");
        $form->addButton("PitchOut\n" . WorldProvider::countWorld("pitchout") . " playing", 0, "textures/items/feather");
        $form->addButton("Fist\n" . $countFist . " playing", 0, "textures/ui/hunger_effect");
        $form->addButton("Combo\n" . WorldProvider::countWorld("combo") . " playing", 0, "textures/items/fish_pufferfish_raw");
        $form->addButton("« Exit");
        $form->sendToPlayer($player);
    }

    public static function openFistForm(Player $player)
    {
        $form = new SimpleForm(function(Player $player, $data){
           if($data === null) return;

           switch($data)
           {
               case 0:
                   if(WorldProvider::countWorld("fist1") > 9) return $player->sendMessage("§c» This arena is currently full.");
                   if (FFAForm::teleportPlayer($player, "fist1"))
                   {
                       KitsAPI::addFistKit($player);
                   }
                   break;
               case 1:
                   $os = PlayerManager::$os[$player->getName()];

                   $array = ["Android", "iOS"];

                   if(in_array($os, $array))
                   {
                       if(WorldProvider::countWorld("fist2") > 9) return $player->sendMessage("§c» This arena is currently full.");
                       if (FFAForm::teleportPlayer($player, "fist2"))
                       {
                           KitsAPI::addFistKit($player);
                       }
                   }else{
                       $player->sendMessage("§c» You must be playing on a touch device to enter this arena.");
                   }

                   break;
               case 2:
                   self::openForm($player);
                   break;
           }
        });
        $form->setTitle("FreeForAll");
        $form->addButton("Fist (All)\n" . WorldProvider::countWorld("fist1") . " playing", 0, "textures/ui/hunger_effect");
        $form->addButton("Fist (PE Only)\n" . WorldProvider::countWorld("fist2") . " playing", 0, "textures/ui/hunger_effect");
        $form->addButton("« Back");
        $form->sendToPlayer($player);
    }

    public static function teleportPlayer(Player $player, string $name): bool
    {
        $level = Server::getInstance()->getLevelByName($name);
        if (is_null($level)) {
            return false;
        } else {
            $player->teleport($level->getSafeSpawn());
            return true;
        }
    }

    public static function addScb(Player $player)
    {
        unset(PlayerJoin::$scoreboard[$player->getName()]);
        PlayerJoin::$scoreboard[$player->getName()] = new FFAScoreboard($player);
        PlayerJoin::$scoreboard[$player->getName()]->sendRemoveObjectivePacket();
        FFAScoreboard::createLines($player);
    }
}