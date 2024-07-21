<?php

namespace practice\forms;

use practice\api\form\CustomForm;
use practice\api\PlayerDataAPI;
use pocketmine\Player;

class SettingsForm
{
    public static function openSettingsForm(Player $player)
    {
        $form = new CustomForm(function(Player $player, $data){
            if($data === null) return;

            PlayerDataAPI::setSetting($player->getName(), "particles_amplifier", $data[0]);

            switch ($data[1])
            {
                case 0:
                    PlayerDataAPI::setSetting($player->getName(), "potions_colour", "default");
                    break;
                case 1:
                    PlayerDataAPI::setSetting($player->getName(), "potions_colour", "black");
                    break;
                case 2:
                    PlayerDataAPI::setSetting($player->getName(), "potions_colour", "grey");
                    break;
                case 3:
                    PlayerDataAPI::setSetting($player->getName(), "potions_colour", "white");
                    break;
                case 4:
                    PlayerDataAPI::setSetting($player->getName(), "potions_colour", "yellow");
                    break;
                case 5:
                    PlayerDataAPI::setSetting($player->getName(), "potions_colour", "orange");
                    break;
                case 6:
                    PlayerDataAPI::setSetting($player->getName(), "potions_colour", "cyan");
                    break;
                case 7:
                    PlayerDataAPI::setSetting($player->getName(), "potions_colour", "green");
                    break;
                case 8:
                    PlayerDataAPI::setSetting($player->getName(), "potions_colour", "blue");
                    break;
                case 9:
                    PlayerDataAPI::setSetting($player->getName(), "potions_colour", "pink");
                    break;
                case 10:
                    PlayerDataAPI::setSetting($player->getName(), "potions_colour", "purple");
                    break;
            }

            switch($data[2])
            {
                case 0:
                    PlayerDataAPI::setSetting($player->getName(), "bow_hit_sound", "disabled");
                    break;
                case 1:
                    PlayerDataAPI::setSetting($player->getName(), "bow_hit_sound", "orb");
                    break;
                case 2:
                    PlayerDataAPI::setSetting($player->getName(), "bow_hit_sound", "anvil");
                    break;
                case 3:
                    PlayerDataAPI::setSetting($player->getName(), "bow_hit_sound", "bell");
                    break;
            }

            PlayerDataAPI::setSetting($player->getName(), "scoreboard", ($data[3]) ? "true" : "false");
            PlayerDataAPI::setSetting($player->getName(), "private_message", ($data[4]) ? "true" : "false");
            PlayerDataAPI::setSetting($player->getName(), "msg_sounds", ($data[5]) ? "true" : "false");
            PlayerDataAPI::setSetting($player->getName(), "duel_requests", ($data[6]) ? "true" : "false");
            PlayerDataAPI::setSetting($player->getName(), "duel_sounds", ($data[7]) ? "true" : "false");
            PlayerDataAPI::setSetting($player->getName(), "lightning_death", ($data[8]) ? "true" : "false");
            PlayerDataAPI::setSetting($player->getName(), "cps_counter", ($data[9]) ? "true" : "false");
            PlayerDataAPI::setSetting($player->getName(), "potions_counter", ($data[10]) ? "true" : "false");
            PlayerDataAPI::setSetting($player->getName(), "reach_counter", ($data[11]) ? "true" : "false");
            PlayerDataAPI::setSetting($player->getName(), "combo_counter", ($data[12]) ? "true" : "false");
            PlayerDataAPI::setSetting($player->getName(), "auto_rekit", ($data[13]) ? "true" : "false");
            PlayerDataAPI::setSetting($player->getName(), "auto_sprint", ($data[14]) ? "true" : "false");

            $player->sendMessage("§a» Your settings have been saved.");
        });
        $form->setTitle("Settings");
        $form->addStepSlider("Extra Particles", ["x0", "x1", "x2", "x3", "x4", "x5"], (int)PlayerDataAPI::getSetting($player->getName(), "particles_amplifier"));
        $form->addStepSlider("Potion Color", ["§cDefault", "§0Black", "§7Grey", "§fWhite", "§eYellow", "§6Orange", "§3Cyan", "§aGreen", "§9Blue", "§dPink", "§5Purple"], self::translateColor(PlayerDataAPI::getSetting($player->getName(), "potions_colour")));
        $form->addStepSlider("Bow Hit Sound", ["Disabled", "Orb", "Anvil", "Bell"], self::translateBowHitSound(PlayerDataAPI::getSetting($player->getName(), "bow_hit_sound")));
        $form->addToggle("« Scoreboard", PlayerDataAPI::getSetting($player->getName(), "scoreboard") === "true");
        $form->addToggle("« Private Messages", PlayerDataAPI::getSetting($player->getName(), "private_message") === "true");
        $form->addToggle("« Messages Sounds", PlayerDataAPI::getSetting($player->getName(), "msg_sounds") === "true");
        $form->addToggle("« Duel Requests", PlayerDataAPI::getSetting($player->getName(), "duel_requests") === "true");
        $form->addToggle("« Duel Sounds", PlayerDataAPI::getSetting($player->getName(), "duel_sounds") === "true");
        $form->addToggle("« Lightning Death", PlayerDataAPI::getSetting($player->getName(), "lightning_death") === "true");
        $form->addToggle("« CPS Counter", PlayerDataAPI::getSetting($player->getName(), "cps_counter") === "true");
        $form->addToggle("« Potion Counter", PlayerDataAPI::getSetting($player->getName(), "potions_counter") === "true");
        $form->addToggle("« Reach Counter", PlayerDataAPI::getSetting($player->getName(), "reach_counter") === "true");
        $form->addToggle("« Combo Counter", PlayerDataAPI::getSetting($player->getName(), "combo_counter") === "true");
        $form->addToggle("« Auto Rekit", PlayerDataAPI::getSetting($player->getName(), "auto_rekit") === "true");
        $form->addToggle("« Auto Sprint §c(Temporary Disabled)§f", PlayerDataAPI::getSetting($player->getName(), "auto_sprint") === "true");
        $form->sendToPlayer($player);
    }


    public static function translateBowHitSound($sound)
    {
        switch($sound)
        {
            case "disabled":
                return 0;
                break;
            case "orb":
                return 1;
                break;
            case "anvil":
                return 2;
                break;
            case "bell":
                return 3;
                break;
        }
    }

    public static function translateColor($color)
    {
        switch ($color) {
            case "black":
                return 1;
                break;
            case "grey":
                return 2;
                break;
            case "white":
                return 3;
                break;
            case "yellow":
                return 4;
                break;
            case "orange":
                return 5;
                break;
            case "cyan":
                return 6;
                break;
            case "green":
                return 7;
                break;
            case "blue":
                return 8;
                break;
            case "pink":
                return 9;
                break;
            case "purple":
                return 10;
                break;
            default:
                return 0;
                break;
        }
    }
}