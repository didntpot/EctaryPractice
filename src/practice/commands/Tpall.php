<?php

namespace practice\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Server;

class Tpall extends PluginCommand
{
    public function __construct($plugin)
    {
        parent::__construct("tpall", $plugin);
        $this->setDescription("Tpall command");
        $this->setPermission("tpall.command");
    }

    public function execute(CommandSender $player, string $commandLabel, array $args)
    {
        if(!$player->hasPermission("tpall.command")) return $player->sendMessage("§cYou do not have permission to use this command");

        foreach(Server::getInstance()->getOnlinePlayers() as $all)
        {
            $all->teleport($player);
            $all->getInventory()->clearAll();
            $all->getArmorInventory()->clearAll();
            $player->sendMessage("§a» You've successfully teleported every players to you.");
        }
    }
}