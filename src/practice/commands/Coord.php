<?php

namespace practice\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use practice\forms\CosmeticsForm;
use practice\party\form\PartyForm;
use practice\api\KitsAPI;

class Coord extends PluginCommand
{
    public function __construct($plugin)
    {
        parent::__construct("coord", $plugin);
        $this->setDescription("Coord command");
        $this->setPermission("coord.command");
    }

    public function execute(CommandSender $player, string $commandLabel, array $args)
    {
        if (!$player->hasPermission("coord.command")) return $player->sendMessage("§cYou do not have permission to use this command");
        $player->sendMessage("§a» {$player->getLocation()}");
    }
}