<?php

namespace practice\party;

use pocketmine\Server;
use practice\Main;
use practice\party\event\PlayerChangeLevel;
use practice\party\event\PlayerQuit;

class PartyLoader
{
    public static function initParty()
    {
        Server::getInstance()->getPluginManager()->registerEvents(new PlayerQuit(), Main::getInstance());
        Server::getInstance()->getPluginManager()->registerEvents(new PlayerChangeLevel(), Main::getInstance());
        PartyProvider::deleteOldLevel();
    }
}