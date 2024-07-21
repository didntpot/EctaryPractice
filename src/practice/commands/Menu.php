<?php

namespace practice\commands;

use practice\forms\PlayerPerksForm;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;

class Menu extends PluginCommand
{
    public function __construct($plugin)
    {
        parent::__construct("menu", $plugin);
        $this->setDescription("Menu command");
    }

    public function execute(CommandSender $player, string $commandLabel, array $args)
    {
        PlayerPerksForm::openForm($player);
    }
}