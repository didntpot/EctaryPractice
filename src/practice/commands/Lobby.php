<?php

namespace practice\commands;

use practice\api\form\ModalForm;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use waterdog\transfercommand\API;

class Lobby extends PluginCommand
{
    public function __construct($plugin)
    {
        parent::__construct("lobby", $plugin);
        $this->setDescription("Lobby command");
    }

    public function execute(CommandSender $player, string $commandLabel, array $args)
    {
        $form = new ModalForm(function (Player $player, $data) {
            if ($data === null) {
            } else {
                if ($data === true) {
                    $player->transfer("ectary.club", 19132);
                }
            }
        });
        $form->setTitle("Lobby");
        $form->setContent("Are you sure that you want to get back to the main lobby?");
        $form->setButton1("Transfer");
        $form->setButton2("« Exit");
        $form->sendToPlayer($player);
    }
}