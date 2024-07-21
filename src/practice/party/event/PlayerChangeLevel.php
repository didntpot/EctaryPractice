<?php


namespace practice\party\event;


use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\Server;
use practice\party\PartyProvider;

class PlayerChangeLevel implements Listener
{

    public function onLevelChange(EntityLevelChangeEvent $event)
    {
        $player = $event->getEntity();
        if ($player instanceof Player) {
            if (PartyProvider::hasParty($player->getName())) {
                $party = PartyProvider::getParty($player->getName());
                if (!is_null($party)) {
                    if ($event->getTarget()->getFolderName() !== $party->getId() and $event->getTarget()->getFolderName() !== Server::getInstance()->getDefaultLevel()->getName()) {
                        PartyProvider::removePlayerFromParty($player);
                    }
                }
            }
        }
    }
}