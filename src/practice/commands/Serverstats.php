<?php


namespace practice\commands;


use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use practice\api\form\CustomForm;
use practice\manager\ServerStatsManager;

class Serverstats extends PluginCommand
{
    public function __construct($plugin)
    {
        parent::__construct("serverstats", $plugin);
        $this->setDescription("Server Stats command");
        $this->setPermission("serverstats.command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if ($sender instanceof Player) {
            if (!$sender->hasPermission($this->getPermission())) return;
            $form = new CustomForm(function (Player $player, $data) {
                return false;
            });
            $form->setTitle("Server Stas");
            $form->addLabel("» 1 hour ago : " . ServerStatsManager::getJoinNumber()["1hour"] . " online(s)");
            $form->addLabel("» 2 hours ago : " . ServerStatsManager::getJoinNumber()["2hour"] . " online(s)\n");
            $form->addLabel("Online platforms :");

            foreach (ServerStatsManager::getPlatformsCount() as $device => $number) {
                $form->addLabel("» " . $device . " : $number");
            }
            $form->sendToPlayer($sender);
        }
    }
}