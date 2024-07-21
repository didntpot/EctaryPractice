<?php

namespace practice\loader;

use practice\Main;
use practice\events\listener\{BlockBreak,
    PlayerChatEvent,
    PlayerCraft,
    PlayerDeath,
    PlayerInventory,
    PlayerJoin,
    BlockPlace,
    PlayerPreLogin,
    PlayerPreprocessEvent,
    PlayerQuit,
    PlayerInteract,
    EntityDamageByEntity,
    PlayerCommandPreprocess,
    ProjectileHitBlock,
    ProjectileLaunch,
    DataPacketReceive,
    CustomDeath,
    EntityShootBow,
    ProjectileHitEntity,
    PlayerDropItem,
    PlayerMove,
    EntityLevelChange,
    PlayerCreation,
    PlayerItemConsume,
    PlayerRespawn,
    InventoryPickupItem, BlockUpdate, BlockForm, PlayerBucket};
use practice\game\events\EventListener;
use pocketmine\Server;

class EventsLoader
{
    public static function initEvents()
    {
        Server::getInstance()->getPluginManager()->registerEvents(new PlayerJoin(), Main::getInstance());
        Server::getInstance()->getPluginManager()->registerEvents(new PlayerQuit(), Main::getInstance());
        Server::getInstance()->getPluginManager()->registerEvents(new PlayerDeath(), Main::getInstance());
        Server::getInstance()->getPluginManager()->registerEvents(new PlayerInteract(), Main::getInstance());
        Server::getInstance()->getPluginManager()->registerEvents(new PlayerInventory(), Main::getInstance());
        Server::getInstance()->getPluginManager()->registerEvents(new BlockBreak(), Main::getInstance());
        Server::getInstance()->getPluginManager()->registerEvents(new BlockPlace(), Main::getInstance());
        Server::getInstance()->getPluginManager()->registerEvents(new PlayerPreprocessEvent(), Main::getInstance());
        Server::getInstance()->getPluginManager()->registerEvents(new PlayerPreLogin(), Main::getInstance());
        Server::getInstance()->getPluginManager()->registerEvents(new EntityDamageByEntity(), Main::getInstance());
        Server::getInstance()->getPluginManager()->registerEvents(new PlayerCommandPreprocess(), Main::getInstance());
        Server::getInstance()->getPluginManager()->registerEvents(new PlayerChatEvent(), Main::getInstance());
        Server::getInstance()->getPluginManager()->registerEvents(new ProjectileHitBlock(), Main::getInstance());
        Server::getInstance()->getPluginManager()->registerEvents(new ProjectileLaunch(), Main::getInstance());
        Server::getInstance()->getPluginManager()->registerEvents(new DataPacketReceive(), Main::getInstance());
        Server::getInstance()->getPluginManager()->registerEvents(new CustomDeath(), Main::getInstance());
        Server::getInstance()->getPluginManager()->registerEvents(new EntityShootBow(), Main::getInstance());
        Server::getInstance()->getPluginManager()->registerEvents(new ProjectileHitEntity(), Main::getInstance());
        Server::getInstance()->getPluginManager()->registerEvents(new PlayerDropItem(), Main::getInstance());
        Server::getInstance()->getPluginManager()->registerEvents(new PlayerMove(), Main::getInstance());
        Server::getInstance()->getPluginManager()->registerEvents(new EventListener(), Main::getInstance());
        Server::getInstance()->getPluginManager()->registerEvents(new EntityLevelChange(), Main::getInstance());
        Server::getInstance()->getPluginManager()->registerEvents(new PlayerCraft(), Main::getInstance());
        Server::getInstance()->getPluginManager()->registerEvents(new PlayerCreation(), Main::getInstance());
        Server::getInstance()->getPluginManager()->registerEvents(new PlayerItemConsume(), Main::getInstance());
        Server::getInstance()->getPluginManager()->registerEvents(new PlayerRespawn(), Main::getInstance());
        Server::getInstance()->getPluginManager()->registerEvents(new InventoryPickupItem(), Main::getInstance());
        Server::getInstance()->getPluginManager()->registerEvents(new BlockForm(), Main::getInstance());
        Server::getInstance()->getPluginManager()->registerEvents(new BlockUpdate(), Main::getInstance());
        Server::getInstance()->getPluginManager()->registerEvents(new PlayerBucket(), Main::getInstance());
    }
}