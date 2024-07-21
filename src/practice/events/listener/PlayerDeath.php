<?php

namespace practice\events\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\{PlayerDeathEvent, PlayerRespawnEvent};
use practice\api\PlayerDataAPI;
use practice\manager\PlayerManager;
use pocketmine\Server;
use practice\manager\LevelManager;
use practice\api\KitsAPI;
use practice\api\LightningAPI;

class PlayerDeath implements Listener
{
    public function onDeath(PlayerDeathEvent $event)
    {
        $player = $event->getPlayer();
        $event->setDrops([]);
        $event->setDeathMessage(null);

        if(isset(CustomDeath::$damager[$player->getName()]))
        {
            $killer = Server::getInstance()->getPlayer(CustomDeath::$damager[$player->getName()]);

            if(!is_null($killer))
            {
                CustomDeath::addStats($player, $killer);
                CustomDeath::reKitPlayer($player, $killer);
                CustomDeath::unsetAllCache($player, $killer);
                #KitsAPI::addLobbyKit($player);
                LightningAPI::spawnLightning($player->getPosition(), $killer);

            }else{
                LevelManager::teleportSpawn($player);
            }
        }else{
            LevelManager::teleportSpawn($player);
        }
    }

    public function onRespawn(PlayerRespawnEvent $event)
    {
        $player = $event->getPlayer();

        KitsAPI::addLobbyKit($player);
    }
}