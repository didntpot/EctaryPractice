<?php

namespace practice\commands;

use pocketmine\command\PluginCommand;
use pocketmine\command\CommandSender;
use pocketmine\Server;

class Tp extends PluginCommand
{
    public function __construct($plugin)
    {
        parent::__construct("tp", $plugin);
        $this->setDescription("Tp command");
        $this->setPermission("tp.command");
    }

    public function execute(CommandSender $player, string $commandLabel, array $args)
    {
        if(!$player->hasPermission("tp.command")) return $player->sendMessage("§cYou do not have permission to use this command");

        if(count($args) < 1) return $player->sendMessage("§c» Command usage: /tp [target player] <destination player>");

        if(count($args) === 1)
        {
            if(isset($args[0]))
            {
                $target = Server::getInstance()->getPlayer($args[0]);
                if(!is_null($target))
                {
                    $player->teleport($target);
                    $player->sendMessage("§a» You've been teleported to {$target->getName()}.");
                }else{
                    $player->sendMessage("§c» Command usage: /tp [target player] <destination player>");
                }
            }else{
                $player->sendMessage("§c» Command usage: /tp [target player] <destination player>");
            }
        }elseif(count($args) === 2)
        {
            if(isset($args[0]) && isset($args[1]))
            {
                $targetOne = Server::getInstance()->getPlayer($args[0]);
                $targetTwo = Server::getInstance()->getPlayer($args[1]);

                if(is_null($targetOne)) return $player->sendMessage("§c» Command usage: /tp [target player] <destination player>");
                if(is_null($targetTwo)) return $player->sendMessage("§c» Command usage: /tp [target player] <destination player>");

                $targetOne->teleport($targetTwo);
                $player->sendMessage("§a» You've successfully teleported {$targetOne->getName()} to {$targetTwo->getName()}.");
            }else{
                $player->sendMessage("§c» Command usage: /tp [target player] <destination player>");
            }
        }
    }
}