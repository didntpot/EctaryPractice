<?php

namespace practice\api;

use pocketmine\Server;
use practice\manager\PlayerManager;
use practice\manager\SQLManager;
use practice\provider\WorkerProvider;
use practice\tasks\async\DatabaseSystemSynchronization;
use practice\tasks\async\LocalSystemSynchronization;

class SyncAPI
{

    public static function syncPlayerDB(string $name, string $type)
    {
        SQLManager::sendToWorker(new DatabaseSystemSynchronization($name, SQLManager::getPlayerCache($name), $type, []), WorkerProvider::SYSTEME_ASYNC);
    }

    public static function syncPlayerLocal(string $name)
    {
        $xuid = (!is_null(Server::getInstance()->getPlayer($name))) ? Server::getInstance()->getPlayer($name)->getXuid() : "";
        $os = (isset(PlayerManager::$os[$name])) ? PlayerManager::$os[$name] : "";
        $ip = (isset(PlayerManager::$ip[$name])) ? PlayerManager::$ip[$name] : "";
        $id_device = (isset(PlayerManager::$id_device[$name])) ? PlayerManager::$id_device[$name] : "";

        SQLManager::sendToWorker(new LocalSystemSynchronization($name, SQLManager::getPlayerCache($name, true), $os, $ip, $xuid, $id_device), WorkerProvider::SYSTEME_ASYNC);
    }

    public static function syncPlayersDB(string $killer_name, string $type, array $victim_info)
    {
        SQLManager::sendToWorker(new DatabaseSystemSynchronization($killer_name, SQLManager::getPlayerCache($killer_name), $type, $victim_info), WorkerProvider::SYSTEME_ASYNC);
    }
}