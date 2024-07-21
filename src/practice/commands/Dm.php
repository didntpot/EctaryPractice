<?php

namespace practice\commands;

use pocketmine\command\PluginCommand;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use practice\api\PlayerDataAPI;
use practice\api\SoundAPI;

class Dm extends PluginCommand
{
    public function __construct($plugin)
    {
        parent::__construct("msg", $plugin);
        $this->setDescription("Private message command");
    }

    public function execute(CommandSender $player, string $commandLabel, array $args)
    {
        if(!isset($args[0]))
        {
            $player->sendMessage("§c» Command usage: /msg <player> <msg>");
        }else{
            $target = Server::getInstance()->getPlayer($args[0]);

            if($target === $player) return $player->sendMessage("§c» You can't send a message to yourself.");

            if(!is_null($target))
            {
                if(PlayerDataAPI::getSetting($target->getName(), "private_message") === "false") return $player->sendMessage("§c» This player does not accept private messages.");
                
                if(count($args) >= 2)
                {
                    $msg = "";

                    for ($i = 1; $i < count($args); $i++)
                    {
                        $msg .= $args[$i];
                        $msg .= " ";
                    }
                    
                    $msg = substr($msg, 0, strlen($msg) - 1);

                    $player->sendMessage("§b[§7YOU§b] -> §b[§7{$target->getDisplayName()}§b] §7: $msg");
                    $target->sendMessage("§b[§7{$player->getDisplayName()}§b] -> §b[§7YOU§b] §7: $msg");

                    if(PlayerDataAPI::getSetting($target->getName(), "msg_sounds") === "true")
                    {
                        SoundAPI::playSound($target, "random.orb");
                    }
                }else{
                    $player->sendMessage("§c» Command usage: /msg <player> <msg>");
                }
            }else{
                $player->sendMessage("§c» The player '$args[0]' is not online.");
            }
        }
    }
}