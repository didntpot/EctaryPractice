<?php

namespace practice\commands;

use practice\api\KitsAPI;
use practice\manager\{
    PlayerManager,
    TimeManager
};
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Server;

class Rekit extends PluginCommand
{
    public function __construct($plugin)
    {
        parent::__construct("rekit", $plugin);
        $this->setDescription("Rekit command");
    }

    public function execute(CommandSender $player, string $commandLabel, array $args)
    {
        if (!isset(PlayerManager::$rekit_time[$player->getName()]) or PlayerManager::$rekit_time[$player->getName()] <= time()) {
            PlayerManager::$rekit_time[$player->getName()] = time() + 59;

            switch ($player->getLevel()->getName()) {
                case "nodebuff":
                    KitsAPI::addNodebuffKit($player);
                    Server::getInstance()->broadcastMessage("§7» {$player->getDisplayName()} rekitted in NoDebuff.");
                    break;
                case "buhc":
                    KitsAPI::addBuildUHCKit($player);
                    Server::getInstance()->broadcastMessage("§7» {$player->getDisplayName()} rekitted in BuildUHC.");
                    break;
                case "debuff":
                    KitsAPI::addDebuffKit($player);
                    Server::getInstance()->broadcastMessage("§7» {$player->getName()} rekitted in Debuff.");
                    break;
                case "build":
                    KitsAPI::addBuildKit($player);
                    Server::getInstance()->broadcastMessage("§7» {$player->getDisplayName()} rekitted in Build.");
                    break;
                case "combo":
                    KitsAPI::addComboKit($player);
                    Server::getInstance()->broadcastMessage("§7» {$player->getDisplayName()} rekitted in Combo.");
                    break;
                case "gapple":
                    KitsAPI::addGappleKit($player);
                    Server::getInstance()->broadcastMessage("§7» {$player->getDisplayName()} rekitted in Gapple.");
                    break;
                case "archer":
                    KitsAPI::addArcherKit($player);
                    Server::getInstance()->broadcastMessage("§7» {$player->getName()} rekitted in Archer.");
                    break;
                case "souprefill":
                    KitsAPI::addSoupRefillKit($player);
                    Server::getInstance()->broadcastMessage("§7» {$player->getName()} rekitted in Soup Refill.");
                    break;
                case "soup":
                    KitsAPI::addSoupRefillKit($player);
                    Server::getInstance()->broadcastMessage("§7» {$player->getName()} rekitted in Soup.");
                    break;
                case "hcf":
                    KitsAPI::addHCFKit($player);
                    Server::getInstance()->broadcastMessage("§7» {$player->getName()} rekitted in HCF.");
                    break;
                case "classic":
                    KitsAPI::addClassicKit($player);
                    Server::getInstance()->broadcastMessage("§7» {$player->getName()} rekitted in Classic.");
                    break;
                case "axe":
                    KitsAPI::addAxeKit($player);
                    Server::getInstance()->broadcastMessage("§7» {$player->getName()} rekitted in Axe.");
                    break;
                case "pitchout":
                    KitsAPI::addPitchoutKit($player);
                    Server::getInstance()->broadcastMessage("§7» {$player->getDisplayName()} rekitted in PitchOut.");
                    break;
                case "koth":
                    KitsAPI::addKothKit($player);
                    Server::getInstance()->broadcastMessage("§7» {$player->getDisplayName()} rekitted in KOTH.");
                    break;
                default:
                    $player->sendMessage("§c» You can't rekit here.");
                    break;
            }
        } else {
            $player->sendMessage("§c» This command is on cooldown for " . TimeManager::timestampToTime(PlayerManager::$rekit_time[$player->getName()])["second"] . " second(s).");
        }
    }
}