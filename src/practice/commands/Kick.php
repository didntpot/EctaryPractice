<?php

namespace practice\commands;

use practice\api\discord\{
    Webhook,
    Message,
    Embed
};
use practice\api\InformationAPI;
use pocketmine\command\PluginCommand;
use pocketmine\command\CommandSender;
use pocketmine\Server;

class Kick extends PluginCommand
{
    public function __construct($plugin)
    {
        parent::__construct("kick", $plugin);
        $this->setDescription("Kick command");
        $this->setPermission("kick.command");
    }

    public function execute(CommandSender $player, string $commandLabel, array $args)
    {
        if (!$player->hasPermission("kick.command")) return $player->sendMessage("§cYou do not have permission to use this command");

        if (!isset($args[0])) {
            $player->sendMessage("§c» Command usage: /kick <player name> <reason>");
        } else {

            $target = Server::getInstance()->getPlayer($args[0]);

            if (!is_null($target)) {
                if (count($args) >= 2) {
                    $reason = "";
                    for ($i = 1; $i < count($args); $i++) {
                        $reason .= $args[$i];
                        $reason .= " ";
                    }
                    $reason = substr($reason, 0, strlen($reason) - 1);

                    $target->kick("§c§lYOU'VE BEEN KICKED§r\n\n§cReason » $reason", false);

                    Server::getInstance()->broadcastMessage("§c» {$target->getName()} has been kicked.");

                    $webHook = new Webhook("https://discord.com/api/webhooks/817260148154040331/Y1vM_zyOEV8TwN6r6rSbm5JVj44MHJZu4F0abt1kemkcdDrbRJF1AawjQRj14GMz2rnO");

                    $msg = new Message();
                    $msg->setUsername("Ectary Network");

                    $embed = new Embed();
                    $embed->setTitle("Kick");
                    $embed->setDescription("**Kicked:** " . $target->getName() . "\n**Reason:** $reason\n**Region:** " . InformationAPI::getServerRegion());
                    $embed->setFooter("Sender: {$player->getName()}");
                    $msg->addEmbed($embed);

                    $webHook->send($msg);
                } else {
                    $player->sendMessage("§c» Please specify a reason.");
                }
            } else {
                $player->sendMessage("§c» The player $args[0] is not online.");
            }
        }
    }
}