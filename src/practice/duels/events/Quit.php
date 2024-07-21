<?php

namespace practice\duels\events;

use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use practice\duels\DuelQueue;
use practice\duels\manager\DuelsManager;
use practice\events\listener\EntityLevelChange;
use practice\party\event\PlayerChangeLevel;

class Quit implements Listener
{

    public function onQuit(PlayerQuitEvent $event)
    {
        if (DuelQueue::isInQueue($event->getPlayer()->getName()))
        {
            DuelQueue::removePlayerQueue($event->getPlayer()->getName());
        }elseif(DuelsManager::isInDuel($event->getPlayer()->getName())){
            $duel = DuelsManager::getDuel($event->getPlayer()->getName());
            if (!is_null($duel))
            {
                $duel->removePlayer($event->getPlayer()->getName());
                $event->getPlayer()->setImmobile(false);
            }
        }
    }

    public function onChangeLevel(EntityLevelChangeEvent $event)
    {
        $entity = $event->getEntity();

        if ($entity instanceof Player)
        {
            if (DuelsManager::isInDuel($entity->getName()))
            {
                $duel = DuelsManager::getDuel($entity->getName());
                if (!is_null($duel) and $event->getTarget()->getFolderName() !== $duel->getId())
                {
                    $duel->removePlayer($entity->getName());
                    $entity->setImmobile(false);
                }
            }
        }
    }
}