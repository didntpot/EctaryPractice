<?php

namespace practice\forms;

use practice\api\form\CustomForm;
use practice\manager\PlayerManager;
use pocketmine\Player;
use practice\api\PlayerDataAPI;

class CosmeticsForm
{
    public static function translateFireworksColor($color)
    {
        switch($color)
        {
            case "white":
                return 0;
                break;
            case "black":
                return 1;
                break;
            case "red":
                return 2;
                break;
            case "blue":
                return 3;
                break;
            case "green":
                return 4;
                break;
            case "yellow":
                return 5;
                break;
        }
    }

    public static function openForm(Player $player)
    {
        $form = new CustomForm(function(Player $player, ?array $data){
            if($data === null) return;

            ### FIREWORKS COLORS ###

            if($player->hasPermission("fireworks_color"))
            {
                switch($data[0])
                {
                    case 0:
                        PlayerDataAPI::setSetting($player->getName(), "victory_fireworks_color", "white");
                        $player->sendMessage("§a» Your fireworks color has been updated.");
                        break;
                    case 1:
                        PlayerDataAPI::setSetting($player->getName(), "victory_fireworks_color", "black");
                        $player->sendMessage("§a» Your fireworks color has been updated.");
                        break;
                    case 2:
                        PlayerDataAPI::setSetting($player->getName(), "victory_fireworks_color", "red");
                        $player->sendMessage("§a» Your fireworks color has been updated.");
                        break;
                    case 3:
                        PlayerDataAPI::setSetting($player->getName(), "victory_fireworks_color", "blue");
                        $player->sendMessage("§a» Your fireworks color has been updated.");
                        break;
                    case 4:
                        PlayerDataAPI::setSetting($player->getName(), "victory_fireworks_color", "green");
                        $player->sendMessage("§a» Your fireworks color has been updated.");
                        break;
                    case 5:
                        PlayerDataAPI::setSetting($player->getName(), "victory_fireworks_color", "yellow");
                        $player->sendMessage("§a» Your fireworks color has been updated.");
                        break;
                }
            }else{
                $player->sendMessage("§c» You do not have permission to use this fireworks color.");
            }

            ### CUSTOM TAG ###
            if($player->hasPermission("custom.tags"))
            {
                $custom = $data[1];
                if($data[1] !== "")
                {
                    if ($custom === PlayerManager::getInformation($player->getName(), "tags")) return $player->sendMessage("§a» Your custom tag has been updated.");
                    if (strlen(str_replace("§", "", $custom)) <= 25) {
                        $player->sendMessage("§a» Your custom tag has been updated.");
                        $custom .= "§r";
                        PlayerManager::setInformation($player->getName(), "tags", $custom);
                    } else {
                        $player->sendMessage("§c» Your custom tag is containing too many characters (Max: 20).");
                    }
                }
            }else{
                $player->sendMessage("§c» You do not have permission to use the custom tag.");
            }

            ### TAGS ###

            switch($data[2])
            {
                case 1:
                    if(!$player->hasPermission("tag.bestww")) return $player->sendMessage("§c» You do not have permission to use this tag.");
                    PlayerManager::setInformation($player->getName(), "tags", "§7[§4BestWW§7]");
                    $player->sendMessage("§a» Your tag has been updated.");
                    break;
                case 2:
                    if(!$player->hasPermission("tag.god")) return $player->sendMessage("§c» You do not have permission to use this tag.");
                    PlayerManager::setInformation($player->getName(), "tags", "§7[§dGod§7]");
                    $player->sendMessage("§a» Your tag has been updated.");
                    break;
                case 3:
                    if(!$player->hasPermission("tag.eboy")) return $player->sendMessage("§c» You do not have permission to use this tag.");
                    PlayerManager::setInformation($player->getName(), "tags", "§7[§9E-boy§7]");
                    $player->sendMessage("§a» Your tag has been updated.");
                    break;
                case 4:
                    if(!$player->hasPermission("tag.egirl")) return $player->sendMessage("§c» You do not have permission to use this tag.");
                    PlayerManager::setInformation($player->getName(), "tags", "§7[§dE-girl§7]");
                    $player->sendMessage("§a» Your tag has been updated.");
                    break;
                case 5:
                    if(!$player->hasPermission("tag.frenchie")) return $player->sendMessage("§c» You do not have permission to use this tag.");
                    PlayerManager::setInformation($player->getName(), "tags", "§7[§bFrenchie§7]");
                    $player->sendMessage("§a» Your tag has been updated.");
                    break;
                case 6:
                    if(!$player->hasPermission("tag.360")) return $player->sendMessage("§c» You do not have permission to use this tag.");
                    PlayerManager::setInformation($player->getName(), "tags", "§7[§a360§7]");
                    $player->sendMessage("§a» Your tag has been updated.");
                    break;
                case 7:
                    if(!$player->hasPermission("tag.microwave")) return $player->sendMessage("§c» You do not have permission to use this tag.");
                    PlayerManager::setInformation($player->getName(), "tags", "§7[§6Microwave§7]");
                    $player->sendMessage("§a» Your tag has been updated.");
                    break;
                case 8:
                    if(!$player->hasPermission("tag.wtf")) return $player->sendMessage("§c» You do not have permission to use this tag.");
                    PlayerManager::setInformation($player->getName(), "tags", "§7[§3WTF§7]");
                    $player->sendMessage("§a» Your tag has been updated.");
                    break;
                case 9:
                    if(!$player->hasPermission("tag.turtle")) return $player->sendMessage("§c» You do not have permission to use this tag.");
                    PlayerManager::setInformation($player->getName(), "tags", "§7[§2Turtle§7]");
                    $player->sendMessage("§a» Your tag has been updated.");
                    break;
                case 10:
                    if(!$player->hasPermission("tag.eventwinner")) return $player->sendMessage("§c» You do not have permission to use this tag.");
                    PlayerManager::setInformation($player->getName(), "tags", "§7[§1Event Winner§7]");
                    $player->sendMessage("§a» Your tag has been updated.");
                    break;
                case 11:
                    PlayerManager::setInformation($player->getName(), "tags", "");
                    $player->sendMessage("§a» Your tag has been removed.");
                    break;
            }

            ### CAPES ###

            switch($data[3])
            {
                case 1:
                    PlayerManager::setCape($player, "Winter 2020");
                    PlayerManager::setInformation($player->getName(), "cape_select", "Winter 2020");
                    $player->sendMessage("§a» Your cape has been updated.");
                    break;
                case 2:
                    PlayerManager::setCape($player, "Submarine");
                    PlayerManager::setInformation($player->getName(), "cape_select", "Submarine");
                    $player->sendMessage("§a» Your cape has been updated.");
                    break;
                case 3:
                    PlayerManager::setCape($player, "Star");
                    PlayerManager::setInformation($player->getName(), "cape_select", "Star");
                    $player->sendMessage("§a» Your cape has been updated.");
                    break;
                case 4:
                    PlayerManager::setCape($player, "Prismarine");
                    PlayerManager::setInformation($player->getName(), "cape_select", "Prismarine");
                    $player->sendMessage("§a» Your cape has been updated.");
                    break;
                case 5:
                    PlayerManager::setCape($player, "Superman");
                    PlayerManager::setInformation($player->getName(), "cape_select", "Superman");
                    $player->sendMessage("§a» Your cape has been updated.");
                    break;
                case 6:
                    PlayerManager::setCape($player, "Christmas 2020");
                    PlayerManager::setInformation($player->getName(), "cape_select", "Christmas 2020");
                    $player->sendMessage("§a» Your cape has been updated.");
                    break;
                case 7:
                    PlayerManager::setCape($player, "Booster");
                    PlayerManager::setInformation($player->getName(), "cape_select", "Booster");
                    $player->sendMessage("§a» Your cape has been updated.");
                    break;
                case 8:
                    PlayerManager::setCape($player, "Heart");
                    PlayerManager::setInformation($player->getName(), "cape_select", "Heart");
                    $player->sendMessage("§a» Your cape has been updated.");
                    break;
                case 9:
                    PlayerManager::removeCape($player);
                    PlayerManager::setInformation($player->getName(), "cape_select", "");
                    $player->sendMessage("§a» Your cape has been removed.");
                    break;
            }

            ### BLOCKS ###

            switch($data[4])
            {
                case 1:
                    PlayerManager::setInformation($player->getName(), "block_select", "sandstone");
                    $player->sendMessage("§a» Your block has been updated.");
                    break;
                case 2:
                    if (!$player->hasPermission("block.black") or !$player->hasPermission("block.all")) return $player->sendMessage("§c» You do not have permission to use this block.");
                    PlayerManager::setInformation($player->getName(), "block_select", "black");
                    $player->sendMessage("§a» Your block has been updated.");
                    break;
                case 3:
                    if (!$player->hasPermission("block.brown") or !$player->hasPermission("block.all")) return $player->sendMessage("§c» You do not have permission to use this block.");
                    PlayerManager::setInformation($player->getName(), "block_select", "brown");
                    $player->sendMessage("§a» Your block has been updated.");
                    break;
                case 4:
                    if (!$player->hasPermission("block.red") or !$player->hasPermission("block.all")) return $player->sendMessage("§c» You do not have permission to use this block.");
                    PlayerManager::setInformation($player->getName(), "block_select", "red");
                    $player->sendMessage("§a» Your block has been updated.");
                    break;
                case 5:
                    if (!$player->hasPermission("block.orange") or !$player->hasPermission("block.all")) return $player->sendMessage("§c» You do not have permission to use this block.");
                    PlayerManager::setInformation($player->getName(), "block_select", "orange");
                    $player->sendMessage("§a» Your block has been updated.");
                    break;
                case 6:
                    if (!$player->hasPermission("block.yellow") or !$player->hasPermission("block.all")) return $player->sendMessage("§c» You do not have permission to use this block.");
                    PlayerManager::setInformation($player->getName(), "block_select", "yellow");
                    $player->sendMessage("§a» Your block has been updated.");
                    break;
                case 7:
                    if (!$player->hasPermission("block.lime") or !$player->hasPermission("block.all")) return $player->sendMessage("§c» You do not have permission to use this block.");
                    PlayerManager::setInformation($player->getName(), "block_select", "lime");
                    $player->sendMessage("§a» Your block has been updated.");
                    break;
                case 8:
                    if (!$player->hasPermission("block.green") or !$player->hasPermission("block.all")) return $player->sendMessage("§c» You do not have permission to use this block.");
                    PlayerManager::setInformation($player->getName(), "block_select", "green");
                    $player->sendMessage("§a» Your block has been updated.");
                    break;
                case 9:
                    if (!$player->hasPermission("block.cyan") or !$player->hasPermission("block.all")) return $player->sendMessage("§c» You do not have permission to use this block.");
                    PlayerManager::setInformation($player->getName(), "block_select", "cyan");
                    $player->sendMessage("§a» Your block has been updated.");
                    break;
                case 10:
                    if (!$player->hasPermission("block.light_blue") or !$player->hasPermission("block.all")) return $player->sendMessage("§c» You do not have permission to use this block.");
                    PlayerManager::setInformation($player->getName(), "block_select", "light_blue");
                    $player->sendMessage("§a» Your block has been updated.");
                    break;
                case 11:
                    if (!$player->hasPermission("block.blue") or !$player->hasPermission("block.all")) return $player->sendMessage("§c» You do not have permission to use this block.");
                    PlayerManager::setInformation($player->getName(), "block_select", "blue");
                    $player->sendMessage("§a» Your block has been updated.");
                    break;
                case 12:
                    if (!$player->hasPermission("block.purple") or !$player->hasPermission("block.all")) return $player->sendMessage("§c» You do not have permission to use this block.");
                    PlayerManager::setInformation($player->getName(), "block_select", "purple");
                    $player->sendMessage("§a» Your block has been updated.");
                    break;
                case 13:
                    if (!$player->hasPermission("block.magenta") or !$player->hasPermission("block.all")) return $player->sendMessage("§c» You do not have permission to use this block.");
                    PlayerManager::setInformation($player->getName(), "block_select", "magenta");
                    $player->sendMessage("§a» Your block has been updated.");
                    break;
                case 14:
                    if (!$player->hasPermission("block.pink") or !$player->hasPermission("block.all")) return $player->sendMessage("§c» You do not have permission to use this block.");
                    PlayerManager::setInformation($player->getName(), "block_select", "pink");
                    $player->sendMessage("§a» Your block has been updated.");
                    break;
            }
        });
        $form->setTitle("Cosmetics");
        $form->addStepSlider("Victory Fireworks Color", ["§fWhite", "§0Black", "§4Red", "§9Blue", "§aGreen", "§eYellow"], self::translateFireworksColor(PlayerDataAPI::getSetting($player->getName(), "victory_fireworks_color")));
        $form->addInput("Custom Tag :", "My Custom Tag");
        $form->addDropDown("Tag :", ["", "§7[§4BestWW§7]", "§7[§dGod§7]", "§7[§9E-boy§7]", "§7[§dE-girl§7]", "§7[§bFrenchie§7]", "§7[§a360§7]", "§7[§6Microwave§7]", "§7[§3WTF§7]", "§7[§2Turtle§7]", "§7[§1Event Winner§7]",  "Remove your tag"]);
        $form->addDropDown("Cape :", ["", "Winter 2020", "Submarine", "Star", "Prismarine", "Superman", "Christmas 2020", "Booster", "Heart", "Remove your cape"]);
        $form->addDropDown("Block :", ["", "Sandstone", "Black Clay", "Brown Clay", "Red Clay", "Orange Clay", "Yellow Clay", "Lime Clay", "Green Clay", "Cyan Clay", "Light Blue Clay", "Blue Clay", "Purple Clay", "Magenta Clay", "Pink Clay"]);
        $form->sendToPlayer($player);
    }
}