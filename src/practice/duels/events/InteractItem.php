<?php


namespace practice\duels\events;


use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\Server;
use practice\api\KitsAPI;
use practice\duels\DuelQueue;
use practice\duels\Duels;
use practice\duels\manager\DuelsManager;
use practice\scoreboard\SpawnScoreboard;
use practice\events\listener\PlayerJoin;

class InteractItem implements Listener
{

    public function onInteract(PlayerInteractEvent $event)
    {
        if ($event->getPlayer()->getInventory()->getItemInHand()->getCustomName() === "§r§cLeave the queue")
        {
            if (DuelQueue::isInQueue($event->getPlayer()->getName()))
            {
                DuelQueue::removePlayerQueue($event->getPlayer()->getName());
                KitsAPI::addLobbyKit($event->getPlayer());
                $event->getPlayer()->sendMessage("§c» You've left the queue.");
                unset(PlayerJoin::$scoreboard[$event->getPlayer()->getName()]);
                PlayerJoin::$scoreboard[$event->getPlayer()->getName()] = new SpawnScoreboard($event->getPlayer());
                PlayerJoin::$scoreboard[$event->getPlayer()->getName()]->sendRemoveObjectivePacket();
                SpawnScoreboard::createLines($event->getPlayer());
            }
        }elseif ($event->getPlayer()->getInventory()->getItemInHand()->getCustomName() === "§r§cLeave spectator mode")
        {
            if (DuelsManager::isInDuel($event->getPlayer()->getName()))
            {
                $duel = DuelsManager::getDuel($event->getPlayer()->getName());

                if (!is_null($duel))
                {
                    if ($duel instanceof Duels)
                    {
                        if ($duel->getPlayerType($event->getPlayer()->getName()) === "spectator")
                        {
                            $duel->removeSpectator($event->getPlayer()->getName());
                        }
                    }
                }
            }
        }elseif ($event->getPlayer()->getInventory()->getItemInHand()->getCustomName() === "§r§bTeleport to a player")
        {
            if (DuelsManager::isInDuel($event->getPlayer()->getName()))
            {
                $duel = DuelsManager::getDuel($event->getPlayer()->getName());

                if (!is_null($duel))
                {
                    if ($duel instanceof Duels)
                    {
                        if ($duel->getPlayerType($event->getPlayer()->getName()) === "spectator")
                        {
                            $players = Server::getInstance()->getPlayer($duel->getPlayers()[array_rand($duel->getPlayers())]);
                            if (!is_null($players)) $event->getPlayer()->teleport($players->asVector3());
                        }
                    }
                }
            }
        }
    }
}