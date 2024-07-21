<?php


namespace practice\commands;


use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\Server;

class Worlds extends PluginCommand
{

    public function __construct($plugin)
    {
        parent::__construct("world", $plugin);
        $this->setDescription("World command");
        $this->setPermission("world.command.permission");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if(!$sender->hasPermission($this->getPermission())) return true;
        if ($sender instanceof Player) {
            if (isset($args[0])) {
                switch ($args[0]) {
                    case "tp":
                        if (!isset($args[1])) return $sender->sendMessage("/world tp (name)");
                        if (!Server::getInstance()->isLevelLoaded($args[1])) Server::getInstance()->loadLevel($args[1]);
                        if (!is_null(Server::getInstance()->getLevelByName($args[1]))) {
                            $sender->teleport(Server::getInstance()->getLevelByName($args[1])->getSafeSpawn());
                        } else {
                            $sender->sendMessage("Monde pas trouver");
                        }
                        break;
                    case "list":
                        $name = [];
                        foreach (Server::getInstance()->getLevels() as $level) {
                            array_push($name, $level->getName());
                        }
                        $name_all = implode(", ", $name);
                        $sender->sendMessage("World list: " . $name_all);
                        break;
                    case "generate":
                        $type = ["FLAT", "DEFAULT"];
                        if (!isset($args[1]) or !isset($args[2]) or !in_array($args[2], $type)) return $sender->sendMessage("/world generate (nom) (FLAT, DEFAULT)");
                        Server::getInstance()->generateLevel($args[1], null, $args[2]);
                        $sender->sendMessage("Monde généré");
                        break;
                }
            } else {
                $sender->sendMessage("/world (tp, list, generate)");
            }
        }
    }
}