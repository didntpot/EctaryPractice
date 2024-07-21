<?php

namespace practice\events\listener;

use practice\scoreboard\SpawnScoreboard;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use practice\api\LightningAPI;
use practice\api\SoundAPI;
use practice\manager\LevelManager;
use practice\manager\PlayerManager;
use practice\loader\ConfigLoader;
use practice\api\CpsAPI;
use pocketmine\Server;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use practice\manager\ServerStatsManager;
use pocketmine\level\Location;
use practice\manager\SQLManager;

class PlayerJoin implements Listener
{
    public static $scoreboard = [];

    public function onJoin(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();
        if (!is_null($player))
        {

            $event->setJoinMessage(null);
            $level = Server::getInstance()->getLevelByName("spawn");
            if (!is_null($level))
            {
                $player->teleport(new Location(0.5000, 18, 25.5000, 180.0, -2.0, $level));
            }
            $player->setNameTag("§c{$player->getDisplayName()}");
            $player->sendMessage("§7————————————————————————————————————————————————\n§bEctary Network\n\n§fWelcome to Ectary Practice, we're currently on season IV.\n\n§7Our forums: https://www.ectary.club\n§7Our discord: https://discord.gg/H3HJXAJ\n§7Our store: https://ectary.buycraft.net\n\n§7————————————————————————————————————————————————");
            PlayerManager::registerPlayer($player);
            CpsAPI::initPlayerClickData($player);
            PlayerJoin::initData($player->getName());
            self::$scoreboard[$player->getName()] = new SpawnScoreboard($player);
            SpawnScoreboard::createLines($player);
        }
    }

    public static function initData($name)
    {
        PlayerManager::$reach[$name] = 0;
        PlayerManager::$combo[$name] = 0;
        PlayerManager::$staff_mode[$name] = false;
        PlayerManager::$staff_chat[$name] = false;
        PlayerManager::$frozen[$name] = false;
    }
}