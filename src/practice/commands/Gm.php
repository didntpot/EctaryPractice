<?php

namespace practice\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Server;

class Gm extends PluginCommand
{
    public function __construct($plugin)
    {
        parent::__construct("gm", $plugin);
        $this->setDescription("Gm command");
        $this->setPermission("gm.command");
        $this->setAliases(['gamemode']);
    }

    public function execute(CommandSender $player, string $commandLabel, array $args)
    {
        if(!$player->hasPermission("gm.command")) return $player->sendMessage("§cYou do not have permission to use this command.");
        if(!isset($args[0])) return $player->sendMessage("§c» Command usage: /gm <0/1/2/3|s/c/a/sp>");

        if(!isset($args[1]))
        {
            switch($args[0])
            {
                case "0":
                case "s":
                case "survival":
                    $player->setGamemode(0);
                    $player->sendMessage("§a» You've set your gamemode to 0.");
                    break;
                case "1":
                case "c":
                case "creative":
                    $player->setGamemode(1);
                    $player->sendMessage("§a» You've set your gamemode to 1.");
                    break;
                case "2":
                case "a":
                case "adventure":
                    $player->setGamemode(2);
                    $player->sendMessage("§a» You've set your gamemode to 2.");
                    break;
                case "3":
                case "spectator":
                    $player->setGamemode(3);
                    $player->sendMessage("§a» You've set your gamemode to 3.");
                    break;
            }
        }

        if(isset($args[1]))
        {
            $target = Server::getInstance()->getPlayer($args[1]);

            if(!is_null($target))
            {
                switch($args[0])
                {
                    case "0":
                    case "survival":
                    $target->setGamemode(0);
                    $player->sendMessage("§a» You've set {$target->getName()}'s gamemode to 0.");
                        break;
                    case "1":
                    case "creative":
                    $target->setGamemode(1);
                    $player->sendMessage("§a» You've set {$target->getName()}'s gamemode to 1.");
                        break;
                    case "2":
                    case "adventure":
                    $target->setGamemode(2);
                    $player->sendMessage("§a» You've set {$target->getName()}'s gamemode to 2.");
                        break;
                    case "3":
                    case "spectator":
                    $target->setGamemode(3);
                    $player->sendMessage("§a» You've set {$target->getName()}'s gamemode to 3.");
                        break;
                }
            }
        }
    }
}