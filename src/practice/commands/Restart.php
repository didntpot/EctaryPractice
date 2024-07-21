<?php

namespace practice\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Server;

class Restart extends PluginCommand
{
    public function __construct($plugin)
    {
        parent::__construct("restart", $plugin);
        $this->setDescription("Restart command");
        $this->setPermission("restart.command");
    }

    public function execute(CommandSender $player, string $commandLabel, array $args)
    {
        if (!$player->hasPermission("restart.command")) return $player->sendMessage("§cYou do not have permission to use this command");

        foreach (Server::getInstance()->getOnlinePlayers() as $players) {
            $players->transfer("ectary.club", 19132);
        }

        Server::getInstance()->shutdown();

        register_shutdown_function(function () {
            pcntl_exec("./start.sh");
        });
    }
}