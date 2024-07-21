<?php

namespace practice\duels;

use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\Server;
use practice\api\InformationAPI;
use practice\api\KitsAPI;
use practice\duels\manager\DuelsManager;
use practice\events\listener\PlayerJoin;
use practice\manager\PlayerManager;

class DuelQueue
{


    public static function searchOpponent(Player $player,  array $duel_config)
    {
        KitsAPI::clear($player, "all");
        $player->getInventory()->setItem(8, Item::get(Item::REDSTONE, 0, 1)->setCustomName("§r§cLeave the queue"));

        if (!empty(DuelsProvider::$duels_queue))
        {
            foreach (DuelsProvider::$duels_queue as $id => $information)
            {
                if ($information["duel"]["name"] === $duel_config["name"] and $information["duel"]["type"] === $duel_config["type"])
                {
                    self::addQueue($player, $id);

                    return;
                }
            }
        }
        self::createQueue($player, $duel_config);
        if (isset(PlayerJoin::$scoreboard[$player->getName()]))
        {
            PlayerJoin::$scoreboard[$player->getName()]
                ->setLine(11, " §bQueue: §f".DuelQueue::getPlayerQueue($player->getName())["queue"]["duel"]["name"])
                ->setLine(12, " §r§f§f")
                ->set();{}
        }
    }

    public static function countQueue($name, $type)
    {
        if (empty(DuelsProvider::$duels_queue)) return 0;

        foreach (DuelsProvider::$duels_queue as $information){
            if ($information["duel"]["type"] === $type and $information["duel"]["name"] === $name) return count($information["players"]);
        }
        return 0;
    }

    public static function countAllQueue()
    {
        return count(DuelsProvider::$duels_queue);
    }

    public static function isInQueue(string $player)
    {
        if (empty(DuelsProvider::$duels_queue)) return false;

        foreach (DuelsProvider::$duels_queue as $information){
            foreach ($information["players"] as $array)
            {
                if ($player === $array) return true;
            }
        }
        return false;
    }

    public static function createQueue(Player $player, array $duel_config)
    {
        DuelsProvider::$duels_queue[] = ["players" => [$player->getName()], "duel" => $duel_config];
        self::startQueue($player);
    }

    public static function addQueue(Player $player, $duel_id)
    {
        DuelsProvider::$duels_queue[$duel_id]["players"][] = $player->getName();
        if (DuelsProvider::$duels_queue[$duel_id]["duel"]["min_player"] != count(DuelsProvider::$duels_queue[$duel_id]["players"]))
        {
            #self::sendMessageQueue($duel_id, "§a» ". $player->getName() ." join queue. (". count(DuelsProvider::$duels_queue[$duel_id]["players"]) ."/". DuelsProvider::$duels_queue[$duel_id]["duel"]["min_player"]. ")");
            Server::getInstance()->getLogger()->info("§a» ". $player->getName() ." join queue. (". count(DuelsProvider::$duels_queue[$duel_id]["players"]) ."/". DuelsProvider::$duels_queue[$duel_id]["duel"]["min_player"]. ")");
        }else{
            $player->getInventory()->setItem(8, Item::get(0));
            DuelsManager::createDuel(DuelsProvider::$duels_queue[$duel_id]["players"],  DuelsProvider::$duels_queue[$duel_id]["duel"]);
            self::deleteQueue($duel_id);
        }
    }

    public static function getPlayerQueue(string $player)
    {
        foreach (DuelsProvider::$duels_queue as $id => $informations){
            foreach ($informations["players"] as $array)
            {
                if ($player === $array) return ["id" => $id, "queue" => $informations];
            }
        }
        return false;
    }

    public static function startQueue(Player $player)
    {
        if (self::getPlayerQueue($player->getName()) !== false)
        {
            $player->sendMessage("§a» You've joined the queue for ". self::getPlayerQueue($player->getName())["queue"]["duel"]["type"]." ".self::getPlayerQueue($player->getName())["queue"]["duel"]["name"].".");
        }
    }

    public static function removePlayerQueue(string $player)
    {
        foreach (DuelsProvider::$duels_queue as $id => $information){
            foreach ($information["players"] as $player_id => $array)
            {
                if ($array === $player)
                {
                    if (count(DuelsProvider::$duels_queue[$id]["players"]) == 1)
                    {
                        unset(DuelsProvider::$duels_queue[$id]);
                    }else{
                        self::sendMessageQueue($id, "§c» $player leave queue.");
                        if ($array === $player) unset(DuelsProvider::$duels_queue[$id]["players"][$player_id]);
                    }
                }
            }
        }
    }

    public static function deleteQueue(string $duel_id)
    {
        if (isset(DuelsProvider::$duels_queue[$duel_id])) unset(DuelsProvider::$duels_queue[$duel_id]);
    }

    public static function sendMessageQueue($duel_id, $message)
    {
        if (isset(DuelsProvider::$duels_queue[$duel_id]) and isset(DuelsProvider::$duels_queue[$duel_id]["players"]))
        {
            foreach (DuelsProvider::$duels_queue[$duel_id]["players"] as $player_t)
            {
                $pp = Server::getInstance()->getPlayer($player_t);
                if (!is_null($pp)) $pp->sendMessage($message);
            }
        }
    }
}