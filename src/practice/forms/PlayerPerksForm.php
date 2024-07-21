<?php

namespace practice\forms;

use practice\Main;
use practice\api\PlayerDataAPI;
use practice\manager\CapesManager;
use practice\manager\TempRankManager;
use practice\api\form\{
    SimpleForm,
    CustomForm
};
use practice\party\form\PartyForm;
use practice\manager\PlayerManager;
use practice\forms\ReportForm;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;

class PlayerPerksForm
{
    public static function openForm(Player $player)
    {
        $form = new SimpleForm(function (Player $player, $data) {
            if ($data === null) return;
            switch ($data) {
                case 0:
                    SettingsForm::openSettingsForm($player);
                    break;
                case 1:
                    CosmeticsForm::openForm($player);
                    break;
                case 2:
                    self::openStatsForm($player);
                    break;
            }
        });
        $form->setTitle("Settings");
        $form->addButton("Settings");
        $form->addButton("Cosmetics");
        $form->addButton("Stats");
        $form->addButton("« Exit");
        $form->sendToPlayer($player);
    }

    public static function openStatsForm(Player $player)
    {
        $form = new SimpleForm(function (Player $player, $data) {
            if ($data === null) return;
            switch ($data) {
                case 0:
                    self::openStatsSearchForm($player);
                    break;
                case 1:
                    self::openForm($player);
                    break;
            }
        });
        $form->setTitle("Stats");
        $division = PlayerManager::getInformation($player->getName(), "division");
        $kill = PlayerManager::getInformation($player->getName(), "kill");
        $death = PlayerManager::getInformation($player->getName(), "death");
        $kill_streak = PlayerManager::getInformation($player->getName(), "kill_streak");
        $loses = PlayerManager::getInformation($player->getName(), "loses");
        $wins = PlayerManager::getInformation($player->getName(), "wins");
        $elo = PlayerManager::getInformation($player->getName(), "elo");
        $os = PlayerManager::$os[$player->getName()];

        $next = "";
        $kill = PlayerManager::getInformation($player->getName(), "kill");

        switch(PlayerManager::getInformation($player->getName(), "division"))
        {
            case "§8[Bronze I]":
                $next = "$kill/25";
                break;
            case "§8[Bronze II]":
                $next = "$kill/75";
                break;
            case "§8[Bronze III]":
                $next = "$kill/150";
                break;
            case "§7[Silver I]":
                $next = "$kill/250";
                break;
            case "§7[Silver II]":
                $next = "$kill/350";
                break;
            case "§7[Silver III]":
                $next = "$kill/500";
                break;
            case "§e[Gold I]":
                $next = "$kill/700";
                break;
            case "§e[Gold II]":
                $next = "$kill/850";
                break;
            case "§e[Gold III]":
                $next = "$kill/1000";
                break;
            case "§3[Platinum I]":
                $next = "$kill/1300";
                break;
            case "§3[Platinum II]":
                $next = "$kill/1600";
                break;
            case "§3[Platinum III]":
                $next = "$kill/1900";
                break;
            case "§b[Diamond I]":
                $next = "$kill/2400";
                break;
            case "§b[Diamond II]":
                $next = "$kill/2800";
                break;
            case "§b[Diamond III]":
                $next = "$kill/3000";
                break;
            case "§9[Challenger I]":
                $next = "$kill/3500";
                break;
            case "§9[Challenger II]":
                $next = "$kill/4000";
                break;
            case "§9[Challenger III]":
                $next = "$kill/5000";
                break;
            case "§c[Master]":
                $next = "$kill/MAX";
                break;
        }

        $kdr = 0.0;

        if($death == 0)
        {
            $kdr = 0.0;
        }else{
            $ratio = $kill / $death;
            if($ratio !== 0){
                $kdr = number_format($ratio, 1);
            }
        }

        $form->setContent("§bYour Ranked Stats »\n§r§fElo: $elo\n§rWins: " . $wins . "\nLosses: $loses\n\n§bYour Casual Stats »\n§r§fDivision: $division §r\nNext Division: $next\nKills: $kill\nDeaths: $death\nKill-streak: $kill_streak \nKDR: $kdr\n\n§bOthers »\n§fOS: $os\n" . (TempRankManager::hasTempGroup($player->getName()) ? "Rank Time Left: " . TempRankManager::getTextTimeGroup($player->getName()) : ""));
        $form->addButton("Search a player stats");
        $form->addButton("« Back");
        $form->sendToPlayer($player);
    }

    public static function openCosmeticsForm(Player $player)
    {
        $form = new SimpleForm(function (Player $player, $data) {
            if ($data === null) return;
            switch ($data) {
                case 0:
                    TagsForm::openTagsForm($player);
                    break;
                case 1:
                    CapeForm::openCapesForm($player);
                    break;
                case 2:
                    PlayerPerksForm::openBuildBlocksForm($player);
                    break;
                case 3:
                    PlayerPerksForm::openForm($player);
                    break;
            }
        });
        $form->setTitle("Cosmetics");
        $form->setContent("Select a category:");
        $form->addButton("Tags");
        $form->addButton("Capes");
        $form->addButton("Blocks");
        $form->addButton("« Back");
        $form->sendToPlayer($player);
    }

    public static function openBuildBlocksForm(Player $player)
    {
        $form = new SimpleForm(function (Player $player, $data) {
            if ($data === null) return null;
            switch ($data) {
                case 0:
                    PlayerManager::setInformation($player->getName(), "block_select", "sandstone");
                    $player->sendMessage("§a» Your settings have been saved.");
                    break;
                case 1:
                    if (!$player->hasPermission("block.black") or !$player->hasPermission("block.all")) return $player->sendMessage("§c» You do not have permission to use this.");
                    PlayerManager::setInformation($player->getName(), "block_select", "black");
                    $player->sendMessage("§a» Your settings have been saved.");
                    break;
                case 2:
                    if (!$player->hasPermission("block.brown") or !$player->hasPermission("block.all")) return $player->sendMessage("§c» You do not have permission to use this.");
                    PlayerManager::setInformation($player->getName(), "block_select", "brown");
                    $player->sendMessage("§a» Your settings have been saved.");
                    break;
                case 3:
                    if (!$player->hasPermission("block.red") or !$player->hasPermission("block.all")) return $player->sendMessage("§c» You do not have permission to use this.");
                    PlayerManager::setInformation($player->getName(), "block_select", "red");
                    $player->sendMessage("§a» Your settings have been saved.");
                    break;
                case 4:
                    if (!$player->hasPermission("block.orange") or !$player->hasPermission("block.all")) return $player->sendMessage("§c» You do not have permission to use this.");
                    PlayerManager::setInformation($player->getName(), "block_select", "orange");
                    $player->sendMessage("§a» Your settings have been saved.");
                    break;
                case 5:
                    if (!$player->hasPermission("block.yellow") or !$player->hasPermission("block.all")) return $player->sendMessage("§c» You do not have permission to use this.");
                    PlayerManager::setInformation($player->getName(), "block_select", "yellow");
                    $player->sendMessage("§a» Your settings have been saved.");
                    break;
                case 6:
                    if (!$player->hasPermission("block.lime") or !$player->hasPermission("block.all")) return $player->sendMessage("§c» You do not have permission to use this.");
                    PlayerManager::setInformation($player->getName(), "block_select", "lime");
                    $player->sendMessage("§a» Your settings have been saved.");
                    break;
                case 7:
                    if (!$player->hasPermission("block.green") or !$player->hasPermission("block.all")) return $player->sendMessage("§c» You do not have permission to use this.");
                    PlayerManager::setInformation($player->getName(), "block_select", "green");
                    $player->sendMessage("§a» Your settings have been saved.");
                    break;
                case 8:
                    if (!$player->hasPermission("block.cyan") or !$player->hasPermission("block.all")) return $player->sendMessage("§c» You do not have permission to use this.");
                    PlayerManager::setInformation($player->getName(), "block_select", "cyan");
                    $player->sendMessage("§a» Your settings have been saved.");
                    break;
                case 9:
                    if (!$player->hasPermission("block.light_blue") or !$player->hasPermission("block.all")) return $player->sendMessage("§c» You do not have permission to use this.");
                    PlayerManager::setInformation($player->getName(), "block_select", "light_blue");
                    $player->sendMessage("§a» Your settings have been saved.");
                    break;
                case 10:
                    if (!$player->hasPermission("block.blue") or !$player->hasPermission("block.all")) return $player->sendMessage("§c» You do not have permission to use this.");
                    PlayerManager::setInformation($player->getName(), "block_select", "blue");
                    $player->sendMessage("§a» Your settings have been saved.");
                    break;
                case 11:
                    if (!$player->hasPermission("block.purple") or !$player->hasPermission("block.all")) return $player->sendMessage("§c» You do not have permission to use this.");
                    PlayerManager::setInformation($player->getName(), "block_select", "purple");
                    $player->sendMessage("§a» Your settings have been saved.");
                    break;
                case 12:
                    if (!$player->hasPermission("block.magenta") or !$player->hasPermission("block.all")) return $player->sendMessage("§c» You do not have permission to use this.");
                    PlayerManager::setInformation($player->getName(), "block_select", "magenta");
                    $player->sendMessage("§a» Your settings have been saved.");
                    break;
                case 13:
                    if (!$player->hasPermission("block.pink") or !$player->hasPermission("block.all")) return $player->sendMessage("§c» You do not have permission to use this.");
                    PlayerManager::setInformation($player->getName(), "block_select", "pink");
                    $player->sendMessage("§a» Your settings have been saved.");
                    break;
                case 14:
                    self::openCosmeticsForm($player);
                    break;
            }
        });
        $form->setTitle("Build Blocks");
        $form->addButton("Sandstone (Default)", 0, "textures/blocks/sandstone_bottom");
        $form->addButton("Black Clay", 0, "textures/blocks/hardened_clay_stained_black");
        $form->addButton("Brown Clay", 0, "textures/blocks/hardened_clay_stained_brown");
        $form->addButton("Red Clay", 0, "textures/blocks/hardened_clay_stained_red");
        $form->addButton("Orange Clay", 0, "textures/blocks/hardened_clay_stained_orange");
        $form->addButton("Yellow Clay", 0, "textures/blocks/hardened_clay_stained_yellow");
        $form->addButton("Lime Clay", 0, "textures/blocks/hardened_clay_stained_lime");
        $form->addButton("Green Clay", 0, "textures/blocks/hardened_clay_stained_green");
        $form->addButton("Cyan Clay", 0, "textures/blocks/hardened_clay_stained_cyan");
        $form->addButton("Light Blue Clay", 0, "textures/blocks/hardened_clay_stained_light_blue");
        $form->addButton("Blue Clay", 0, "textures/blocks/hardened_clay_stained_blue");
        $form->addButton("Purple Clay", 0, "textures/blocks/hardened_clay_stained_purple");
        $form->addButton("Magenta Clay", 0, "textures/blocks/hardened_clay_stained_magenta");
        $form->addButton("Pink Clay", 0, "textures/blocks/hardened_clay_stained_pink");
        $form->addButton("« Back");
        $form->sendToPlayer($player);
    }

    public static function openStatsSearchForm(Player $player)
    {
        $form = new CustomForm(function (Player $player, ?array $data) {
            if ($data === null) {
                return true;
            } else {
                if (isset($data[0])) {
                    $target = Server::getInstance()->getPlayer($data[0]);
                    if (!is_null($target)) {
                        self::openStatsSearchedForm($player, $target->getName());
                    } else {
                        $player->sendMessage("§c» This player is not online or does not exist.");
                    }
                }
            }
        });
        $form->setTitle("Stats");
        $form->addInput("Search a player stats :", "...");
        $form->sendToPlayer($player);
    }

    public static function openStatsSearchedForm(Player $player, string $target)
    {
        if (PlayerManager::isSync($target)) return;
        $form = new SimpleForm(function (Player $player, $data) {
            if ($data === null) return;
            switch ($data) {
                case 0:
                    self::openStatsSearchForm($player);
                    break;
                case 1:
                    self::openForm($player);
                    break;
            }
        });
        $form->setTitle("$target's Stats");
        $division = PlayerManager::getInformation($target, "division");
        $kill = PlayerManager::getInformation($target, "kill");
        $death = PlayerManager::getInformation($target, "death");
        $kill_streak = PlayerManager::getInformation($target, "kill_streak");
        $loses = PlayerManager::getInformation($target, "loses");
        $wins = PlayerManager::getInformation($target, "wins");
        $elo = PlayerManager::getInformation($target, "elo");
        $os = PlayerManager::$os[($target)];

        $kdr = 0.0;

        if($death == 0)
        {
            $kdr = 0.0;
        }else{
            $ratio = $kill / $death;
            if($ratio !== 0){
                $kdr = number_format($ratio, 1);
            }
        }

        $form->setContent("§b{$target}'s Ranked Stats »\n§r§fElo: $elo\n§rWins: " . $wins . "\nLosses: $loses\n\n§b{$target}'s Casual Stats »\n§r§fDivision: $division §r\nKills: $kill\nDeaths: $death\nKill-streak: $kill_streak \nKDR: $kdr\n\n§bOthers »\n§fOS: $os\n" . (TempRankManager::hasTempGroup($target) ? "Rank Time Left: " . TempRankManager::getTextTimeGroup($target) : ""));
        $form->addButton("Search a player stats");
        $form->addButton("« Back");
        $form->sendToPlayer($player);
    }

    public static function openSettingsForm(Player $player)
    {
        $form = new SimpleForm(function (Player $player, $data) {
            if ($data === null) return;
            switch ($data) {
                case 0:
                    self::openScoreboardForm($player);
                    break;
                case 1:
                    self::openParticlesAmplifierForm($player);
                    break;
                case 2:
                    self::openPotionsCounterForm($player);
                    break;
                case 3:
                    self::openCPSCounterForm($player);
                    break;
                case 4:
                    self::openPotionsColorForm($player);
                    break;
                case 5:
                    self::openReachCounterForm($player);
                    break;
                case 6:
                    self::openComboCounterForm($player);
                    break;
                case 7:
                    self::openAutoRekitForm($player);
                    break;
                case 8:
                    self::openAutoSprintForm($player);
                    break;
                case 9:
                    PlayerPerksForm::openForm($player);
                    break;
            }
        });
        $form->setTitle("Settings");
        $form->addButton("Scoreboard");
        $form->addButton("Particles Amplifier");
        $form->addButton("Potions Counter");
        $form->addButton("CPS Counter");
        $form->addButton("Potions Color");
        $form->addButton("Reach Counter");
        $form->addButton("Combo Counter");
        $form->addButton("Auto-Rekit");
        $form->addButton("Auto-Sprint");
        $form->addButton("« Back");
        $form->sendToPlayer($player);
    }

    public static function openScoreboardForm(Player $player)
    {
        $form = new CustomForm(function (Player $player, $data) {
            if ($data === null) return;
            PlayerDataAPI::setSetting($player->getName(), "scoreboard", ($data[0]) ? "true" : "false");
            $player->sendMessage("§a» Your settings have been saved.");
        });
        $form->addToggle("Manage scoreboard visibility", PlayerDataAPI::getSetting($player->getName(), "scoreboard") === "true");
        $form->setTitle("Scoreboard");
        $form->sendToPlayer($player);
    }

    public static function openParticlesAmplifierForm(Player $player)
    {
        $form = new CustomForm(function (Player $player, $data) {
            if ($data === null) return;
            PlayerDataAPI::setSetting($player->getName(), "particles_amplifier", $data[0]);
            $player->sendMessage("§a» Your settings have been saved.");
        });
        $form->setTitle("Particles Amplifier");
        $form->addStepSlider("Manage Particles Amplifier", ["0", "1", "2", "3", "4", "5"], (int)PlayerDataAPI::getSetting($player->getName(), "particles_amplifier"));
        $form->sendToPlayer($player);
    }

    public static function openPotionsCounterForm(Player $player)
    {
        $form = new CustomForm(function (Player $player, $data) {
            if ($data === null) return;
            PlayerDataAPI::setSetting($player->getName(), "potions_counter", ($data[0]) ? "true" : "false");
            $player->sendMessage("§a» Your settings have been saved.");
        });
        $form->setTitle("Potions Counter");
        $form->addToggle("Manage potions counter visibility", PlayerDataAPI::getSetting($player->getName(), "potions_counter") === "true");

        $form->sendToPlayer($player);
    }

    public static function openCPSCounterForm(Player $player)
    {
        $form = new CustomForm(function (Player $player, $data) {
            if ($data === null) return;
            PlayerDataAPI::setSetting($player->getName(), "cps_counter", ($data[0]) ? "true" : "false");
            $player->sendMessage("§a» Your settings have been saved.");
        });
        $form->setTitle("CPS Counter");
        $form->addToggle("Manage CPS visibility", PlayerDataAPI::getSetting($player->getName(), "cps_counter") === "true");
        $form->sendToPlayer($player);
    }

    public static function openPotionsColorForm(Player $player)
    {
        $form = new CustomForm(function (Player $player, $data) {
            if ($data === null) return;
            if ($data[0] === null) {
            } else {
                switch ($data[0]) {
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
                $player->sendMessage("§a» Your settings have been saved.");
            }
        });
        $form->setTitle("Potions Color");
        $form->addStepSlider("Select a color", ["Default", "§0Black", "§7Grey", "§fWhite", "§eYellow", "§6Orange", "§3Cyan", "§aGreen", "§9Blue", "§dPink", "§5Purple"], self::translateColor(PlayerDataAPI::getSetting($player->getName(), "potions_colour")));
        $form->sendToPlayer($player);
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

    public static function openReachCounterForm(Player $player)
    {
        $form = new CustomForm(function (Player $player, $data) {
            if ($data === null) return;
            PlayerDataAPI::setSetting($player->getName(), "reach_counter", ($data[0]) ? "true" : "false");
        });
        $form->setTitle("Reach Counter");
        $form->addToggle("Manage Reach visibility", PlayerDataAPI::getSetting($player->getName(), "reach_counter") === "true");
        $form->sendToPlayer($player);
    }

    public static function openComboCounterForm(Player $player)
    {
        $form = new CustomForm(function (Player $player, $data) {
            if ($data === null) return;
            PlayerDataAPI::setSetting($player->getName(), "combo_counter", ($data[0]) ? "true" : "false");
            $player->sendMessage("§a» Your settings have been saved.");
        });
        $form->setTitle("Combo Counter");
        $form->addToggle("Manage Combo visibility", PlayerDataAPI::getSetting($player->getName(), "combo_counter") === "true");

        $form->sendToPlayer($player);
    }

    public static function openAutoRekitForm(Player $player)
    {
        $form = new CustomForm(function (Player $player, $data) {
            if ($data === null) return;
            PlayerDataAPI::setSetting($player->getName(), "auto_rekit", ($data[0]) ? "true" : "false");
            $player->sendMessage("§a» Your settings have been saved.");

        });
        $form->setTitle("Auto-Rekit");
        $form->addToggle("Manage Auto-Rekit", PlayerDataAPI::getSetting($player->getName(), "auto_rekit") === "true");

        $form->sendToPlayer($player);
    }

    public static function openAutoSprintForm(Player $player)
    {
        $form = new CustomForm(function (Player $player, $data) {
            if ($data === null) return;
            PlayerDataAPI::setSetting($player->getName(), "auto_sprint", ($data[0]) ? "true" : "false");
            $player->sendMessage("§a» Your settings have been saved.");
        });
        $form->setTitle("Auto-Sprint");
        $form->addToggle("Manage Auto-Sprint", PlayerDataAPI::getSetting($player->getName(), "auto_sprint") === "true");

        $form->sendToPlayer($player);
    }
}