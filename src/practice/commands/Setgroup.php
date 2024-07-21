<?php


namespace practice\commands;


use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Server;
use practice\manager\GroupManager;
use practice\manager\PlayerManager;
use practice\manager\SQLManager;

class Setgroup extends PluginCommand
{

    public function __construct($plugin)
    {
        parent::__construct("setgroup", $plugin);
        $this->setDescription("Setgoup command");
        $this->setPermission("setgroup.command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if ($sender->hasPermission($this->getPermission()))
        {
            if (!isset($args[0]) or !in_array($args[0], GroupManager::getAllGroupName())) return $sender->sendMessage("§c» Enter an existing group. (". implode(", ", GroupManager::getAllGroupName()).")");
            $group = $args[0];

            $name = "";
            for ($i = 1; $i < count($args); $i++) {
                $name .= $args[$i];
                $name .= " ";
            }

            $player = Server::getInstance()->getPlayer($args[1]);
            if (!is_null($player))
            {
                PlayerManager::setInformation($player->getName(), "group", $group);
                $sender->sendMessage("§a» The rank has been applied to ". $player->getName().". (". $group.")");
                return;
            }

            $name = (!is_null($player)) ? $player->getName() : trim($name);
            if (!isset($args[1])) return $sender->sendMessage("§c» Enter a valid name please.");
            SQLManager::mysqlQuerry('UPDATE `players` SET `group` = "'. $group.'" WHERE `name` = "'. $name.'"');
            $sender->sendMessage("§a» The rank has been applied to ". $name." in the database. (". $group.")");
        }
    }
}