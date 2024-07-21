<?php

namespace practice\duels;

use pocketmine\Server;
use practice\duels\events\BlockBreak;
use practice\duels\events\BlockPlace;
use practice\duels\events\EntityDamage as EntityDamageDuel;
use practice\duels\events\InteractItem;
use practice\duels\events\Quit;
use practice\duels\manager\DuelsManager;
use practice\Main;

class DuelsLoader
{
    public static function initDuels()
    {
        Server::getInstance()->getPluginManager()->registerEvents(new Quit(), Main::getInstance());
        Server::getInstance()->getPluginManager()->registerEvents(new InteractItem(), Main::getInstance());
        Server::getInstance()->getPluginManager()->registerEvents(new EntityDamageDuel(), Main::getInstance());
        Server::getInstance()->getPluginManager()->registerEvents(new BlockBreak(), Main::getInstance());
        Server::getInstance()->getPluginManager()->registerEvents(new BlockPlace(), Main::getInstance());
        DuelsManager::deleteOldLevel();
        //DuelsManager::createDuel(["MatroxMC"], DuelsProvider::LADDER_UNRANKED[0]);
    }
}