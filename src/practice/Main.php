<?php

namespace practice;

use practice\loader\{EventsLoader,
    ConfigLoader,
    GroupsLoader,
    TasksLoader,
    CommandsLoader,
    WorkerLoader,
    WorldsLoader,
    SkinLoader
};
use practice\api\gui\InvMenuHandler;
use pocketmine\level\generator\Generator;
use pocketmine\level\generator\GeneratorManager;
use pocketmine\Server;
use pocketmine\Thread;
use practice\api\InformationAPI;
use practice\Optimization\Chunk\CustomChunkGenerator;
use practice\party\PartyProvider;
use practice\manager\{CapesManager, SQLManager, ItemsManager, EntityManager, TagsManager, WebManager};
use practice\duels\DuelsLoader;
use practice\party\PartyLoader;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase
{
    private static $instance;

    public function onEnable()
    {
        self::$instance = $this;

        //Init ressource

        ConfigLoader::loadRessources();
        WorkerLoader::initWorker();
        InformationAPI::initRegion();

        //Init database
        SQLManager::initSQL();
        TagsManager::initCacheTags();
        CapesManager::initCacheCape();
        GroupsLoader::initPermission();

        //Init game
        EventsLoader::initEvents();
        TasksLoader::initTasks();
        CommandsLoader::initCommands();
        WorldsLoader::initWorlds();
        ItemsManager::initItems();
        EntityManager::init();
        SkinLoader::load();

        PartyLoader::initParty();

        DuelsLoader::initDuels();
        InformationAPI::sendAllServerStatus();

        //Init Web
        WebManager::startWeb();
    }

    public function onDisable()
    {
        PartyProvider::deleteAllWorld();
        WebManager::stopWeb();
    }

    public static function getInstance(): Main
    {
        return self::$instance;
    }
}

