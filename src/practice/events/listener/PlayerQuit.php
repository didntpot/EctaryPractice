<?php

namespace practice\events\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use practice\api\{KitsAPI, PlayerDataAPI, SyncAPI, CpsAPI};
use practice\manager\{GroupManager, InstanceManager, PlayerManager, SQLManager};
use pocketmine\Server;
use practice\duels\events\BlockPlace;
use practice\party\PartyProvider;

class PlayerQuit implements Listener
{
    public function onQuit(PlayerQuitEvent $event)
    {
        $player = $event->getPlayer();

        if (PartyProvider::hasParty($player->getName()))
        {
            $party = PartyProvider::getParty($player->getName());
            if(!is_null($party))
            {
                if ($party->isLeader($player->getName()))
                {
                    PartyProvider::removePlayerFromParty($player);
                }
            }
        }

        $event->setQuitMessage(null);
        KitsAPI::clear($player, "all");
        $player->teleport(Server::getInstance()->getLevelByName("spawn")->getSafeSpawn());
        PlayerManager::setInformation($player->getName(), "serveur", "", false);
        if (!PlayerManager::isSync($player->getName())) SyncAPI::syncPlayerDB($player->getName(), "all");

        InstanceManager::unsetAll($player->getName());

        unset(BlockPlace::$blocks[$player->getName()]);
    }
}