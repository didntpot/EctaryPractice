<?php

namespace practice\tasks;

use practice\api\CpsAPI;
use practice\api\PlayerDataAPI;
use practice\api\SyncAPI;
use practice\manager\PlayerManager;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use JD\Main;

class ModsHud extends Task
{
    public function onRun(int $currentTick)
    {
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if (!PlayerManager::isSync($player->getName()))
            {
                $tip = "";

                if (PlayerDataAPI::getSetting($player->getName(), "cps_counter") === "true") {
                    $tip .= "§bCPS§7: §f" . Main::getInstance()->getClickHandler()->getCps($player) . " ";
                }

                if (PlayerDataAPI::getSetting($player->getName(), "potions_counter") === "true") {
                    $tip .= "§bPots§7: §f" . PlayerManager::getPots($player) . " ";
                }

                if (isset(PlayerManager::$reach[$player->getName()]) && PlayerDataAPI::getSetting($player->getName(), "reach_counter") === "true") {
                    $tip .= "§bReach§7: §f" . PlayerManager::$reach[$player->getName()] . " ";
                }

                if (isset(PlayerManager::$combo[$player->getName()]) && PlayerDataAPI::getSetting($player->getName(), "combo_counter") === "true") {
                    $tip .= "§bCombo§7: §f" . PlayerManager::$combo[$player->getName()] . " ";
                }

                if (isset(PlayerManager::$staff_mode[$player->getName()]) && PlayerManager::$staff_mode[$player->getName()] == true) return;
                $player->sendTip($tip);
            }
        }
    }
}