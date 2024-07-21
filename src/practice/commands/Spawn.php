<?php

namespace practice\commands;

use practice\Main;
use practice\manager\{
    LevelManager,
    TimeManager,
    PlayerManager
};
use practice\api\KitsAPI;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class Spawn extends PluginCommand
{
    //Config du cooldown de la command
    const TIME_COOLDOWN = 4;

    public function __construct($plugin)
    {
        parent::__construct("spawn", $plugin);
        $this->setDescription("Spawn command");
    }

    public $cooldown = [];

    public function execute(CommandSender $player, string $commandLabel, array $args)
    {
        if ($player instanceof Player) {
            if (!isset($this->cooldown[$player->getLowerCaseName()]) or $this->cooldown[$player->getLowerCaseName()] <= time()) {
                $this->cooldown[$player->getLowerCaseName()] = time() + self::TIME_COOLDOWN;
                LevelManager::teleportSpawn($player);

                unset(PlayerManager::$pearl_time[$player->getName()]);
                $player->setXpLevel(0);
                PlayerManager::$reach[$player->getName()] = 0;

                if(isset(PlayerManager::$staff_mode[$player->getName()]) && PlayerManager::$staff_mode[$player->getName()] === true) return;
                KitsAPI::addLobbyKit($player);
            } else {
                $player->sendMessage("§c» This command is on cooldown for " . TimeManager::timestampToTime($this->cooldown[$player->getLowerCaseName()])["second"] . " second(s).");
            }
        }
    }
}
