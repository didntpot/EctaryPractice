<?php

namespace practice\tasks;

use practice\api\PlayerDataAPI;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\Player;
use practice\api\SoundAPI;
use practice\manager\PlayerManager;

class DivisionRankup extends Task
{
    public function onRun(int $currentTick)
    {
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if (PlayerManager::isSync($player->getName())) continue;
            switch (PlayerManager::getInformation($player->getName(), "kill")) {
                case 25:
                    $this->rankup($player, "§8[Bronze II]");
                    break;
                case 75:
                    $this->rankup($player, "§8[Bronze III]");
                    break;
                case 150:
                    $this->rankup($player, "§7[Silver I]");
                    break;
                case 250:
                    $this->rankup($player, "§7[Silver II]");
                    break;
                case 350:
                    $this->rankup($player, "§7[Silver III]");
                    break;
                case 500:
                    $this->rankup($player, "§e[Gold I]");
                    break;
                case 700:
                    $this->rankup($player, "§e[Gold II]");
                    break;
                case 850:
                    $this->rankup($player, "§e[Gold III]");
                    break;
                case 1000:
                    $this->rankup($player, "§3[Platinum I]");
                    break;
                case 1300:
                    $this->rankup($player, "§3[Platinum II]");
                    break;
                case 1600:
                    $this->rankup($player, "§3[Platinum III]");
                    break;
                case 1900:
                    $this->rankup($player, "§b[Diamond I]");
                    break;
                case 2400:
                    $this->rankup($player, "§b[Diamond II]");
                    break;
                case 2800:
                    $this->rankup($player, "§b[Diamond III]");
                    break;
                case 3000:
                    $this->rankup($player, "§9[Challenger I]");
                    break;
                case 3500:
                    $this->rankup($player, "§9[Challenger II]");
                    break;
                case 4000:
                    $this->rankup($player, "§9[Challenger III]");
                    break;
                case 5000:
                    $this->rankup($player, "§c[Master I]");
                    break;
            }
        }
    }

    public function rankup(Player $player, string $division)
    {
        $rand = mt_rand(30, 55);
        PlayerManager::setInformation($player->getName(), "kill", (int)PlayerManager::getInformation($player->getName(), "kill") + 1);
        PlayerManager::setInformation($player->getName(), "coins", PlayerManager::getInformation($player->getName(), "coins") + $rand);
        PlayerManager::setInformation($player->getName(), "division", $division);
        
        $player->sendMessage("§a» §lCongratulations, §r§ayou've ranked up to $division"."§r§a.");
        $player->addTitle("§a§lRankup!§r", "$division");

        SoundAPI::playSound($player, "random.levelup");
    }
}