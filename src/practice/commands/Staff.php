<?php

namespace practice\commands;

use practice\manager\PlayerManager;
use practice\api\KitsAPI;
use pocketmine\command\PluginCommand;
use pocketmine\command\CommandSender;

class Staff extends PluginCommand
{
    public function __construct($plugin)
    {
        parent::__construct("staff", $plugin);
        $this->setDescription("Staff command");
        $this->setPermission("staff.command");
    }

    public function execute(CommandSender $player, string $commandLabel, array $args)
    {
        if(!$player->hasPermission("staff.command")) return;
        if (isset(PlayerManager::$staff_mode[$player->getName()])) {
            switch (PlayerManager::$staff_mode[$player->getName()]) {
                case true:
                    KitsAPI::addLobbyKit($player);
                    PlayerManager::$staff_mode[$player->getName()] = false;
                    PlayerManager::$staff_chat[$player->getName()] = false;
                    $player->setNameTag("§c{$player->getDisplayName()}");
                    break;
                case false:
                    PlayerManager::$staff_mode[$player->getName()] = true;
                    PlayerManager::$staff_chat[$player->getName()] = false;
                    KitsAPI::addStaffKit($player);
                    $player->setNameTag("§c{$player->getDisplayName()}\n§aVanished");
                    break;
            }
        }
    }
}