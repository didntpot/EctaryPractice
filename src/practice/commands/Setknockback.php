<?php

namespace practice\commands;

use practice\Main;
use practice\manager\KnockbackManager;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\utils\Config;

class Setknockback extends PluginCommand
{
    public function __construct($plugin)
    {
        parent::__construct("setknockback", $plugin);
        $this->setDescription("Setknockback command");
        $this->setPermission("setknockback.command");
        $this->setAliases(["setkb"]);
    }

    public function execute(CommandSender $player, string $commandLabel, array $args)
    {
        if (!$player->hasPermission("setknockback.command")) return $player->sendMessage("§cYou do not have permission to use this command");

        if (isset($args[0])) {
            if (is_numeric($args[0])) {
                if (isset($args[1])) {
                    if ($args[1] === "v") {
                        KnockbackManager::$vertical_knockback = $args[0];
                        $config = new Config(Main::getInstance()->getDataFolder() . "server.yml", Config::YAML);
                        $config->set("vertical_knockback", $args[0]);
                        $config->save();
                        $player->sendMessage("vertical_knockback: $args[0]");
                    }
                    if ($args[1] === "h") {
                        KnockbackManager::$horizontal_knockback = $args[0];
                        $config = new Config(Main::getInstance()->getDataFolder() . "server.yml", Config::YAML);
                        $config->set("horizontal_knockback", $args[0]);
                        $config->save();
                        $player->sendMessage("horizontal_knockback: $args[0]");
                    }
                }
            } else {
                $player->sendMessage("Must be numeric.");
            }
        } else {
            $player->sendMessage("/setkb <int>");
        }
    }
}