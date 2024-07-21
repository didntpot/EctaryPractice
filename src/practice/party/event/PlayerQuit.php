<?php

namespace practice\party\event;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use practice\party\PartyProvider;

class PlayerQuit implements Listener
{
    public function PlayerQuitServerParty(PlayerQuitEvent $event)
    {
        PartyProvider::removePlayerFromParty($event->getPlayer());
    }
}