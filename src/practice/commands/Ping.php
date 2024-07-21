<?php

namespace practice\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Server;

class Ping extends PluginCommand
{
    public function __construct($plugin)
    {
        parent::__construct("ping", $plugin);
        $this->setDescription("Ping command");
    }

    public function execute(CommandSender $player, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            $player->sendMessage("§a» Your ping is: {$player->getPing()}ms.");
        } else {

            $target = Server::getInstance()->getPlayer($args[0]);

            if (!is_null($target)) {
                $player->sendMessage("§a» {$target->getName()}'s ping is: {$target->getPing()} ms.");
            } else {
                $player->sendMessage("§c» This player is offline.");
            }
        }
    }
}