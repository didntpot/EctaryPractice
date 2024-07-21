<?php

namespace practice\commands;

use practice\forms\ReportForm;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;

class Report extends PluginCommand
{
    public function __construct($plugin)
    {
        parent::__construct("report", $plugin);
        $this->setDescription("Report command");
    }

    public function execute(CommandSender $player, string $commandLabel, array $args)
    {
        ReportForm::openCommandSelectionForm($player);
    }
}