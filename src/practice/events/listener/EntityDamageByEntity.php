<?php

namespace practice\events\listener;

use pocketmine\scheduler\Task;
use practice\api\PlayerDataAPI;
use practice\api\SoundAPI;
use practice\Main;
use practice\manager\{
    PlayerManager,
    KnockbackManager
};
use practice\party\PartyProvider;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\Player;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\event\entity\EntityDamageEvent;
use practice\duels\manager\DuelsManager;
use pocketmine\entity\Entity;
use practice\duels\events\BlockPlace;

class EntityDamageByEntity implements Listener
{

    public function onEntityDamageByEntity(EntityDamageByEntityEvent $event)
    {
        $player = $event->getEntity();
        $damager = $event->getDamager();
        
        if($player->getLevel()->getName() == "kitroom")
        {
            $event->setCancelled();
        }

        if($player->getLevel()->getName() == "pitchout")
        {
            if($player->getY() > 83)
            {
                $event->setCancelled();
            }
        }

        $block = $player->getLevel()->getBlock($player->floor()->subtract(0, 1));
        $block1 = $player->getLevel()->getBlock($player->floor()->subtract(0, 2));

        if($block->getId() == 99 && $block->getDamage() == 14)
        {
            $event->setCancelled();
        }

        if($block1->getId() == 99 && $block1->getDamage() == 14)
        {
            $event->setCancelled();
        }

        /*
         * CHECK INFORMATION
         */
        if ($player->getLevel()->getName() === "spawn") $event->setCancelled();
        if($player instanceof Player and DuelsManager::isInDuel($player->getName()))
        {
            $duel = DuelsManager::getDuel($player->getName());
            if(!is_null($duel) and $duel->getKit() === "spleef") $event->setCancelled();
        }

        /*
         * BOW DAMAGE
         */
        if($event->getCause() !== EntityDamageEvent::CAUSE_PROJECTILE)
        {
            if($player instanceof Player)
            {
                if($player->getLevel()->getName() == "combo")
                {
                    $event->setAttackCooldown(2);
                }
                if($player->getLevel()->getName() == "sumo")
                {
                    $event->setAttackCooldown(8);
                }
                if($player->getLevel()->getName() == "fist1" or $player->getLevel()->getName() == "fist2")
                {
                    $event->setAttackCooldown(8);
                }
            }
        }

        /*
         * STAFF MOD
         */
        if ($player instanceof Player && $damager instanceof Player) {
            if (isset(PlayerManager::$frozen[$player->getName()]) && $damager->getInventory()->getItemInHand()->getCustomName() === "§r§bFreeze a player") self::freezePlayer($player, $damager);
            if($damager->getInventory()->getItemInHand()->getCustomName() === "§r§bPing Checker") self::sendPing($player, $damager, $event);
        }

        if (!$event->isCancelled() && $player instanceof Player && $damager instanceof Player) {
            PlayerManager::$reach[$damager->getName()] = round($player->distance($damager), 1);
            self::criticalHit($damager, $player, PlayerDataAPI::getSetting($damager->getName(), "particles_amplifier"));

            if(!isset(PlayerManager::$combo[$damager->getName()]))
            {
                PlayerManager:$combo[$damager->getName()] = 1;
            }else{
                PlayerManager::$combo[$damager->getName()] = PlayerManager::$combo[$damager->getName()]+1;
            }

            if(!isset(PlayerManager::$combo[$player->getName()]))
            {
                PlayerManager::$combo[$player->getName()] = 0;
            }else{
                PlayerManager::$combo[$player->getName()] = 0;
            }

            #### ANTI INTERFERING ###

            if ($player->getLevel()->getName() !== "build" && $player->getLevel()->getName() !== "pitchout" && $player->getLevel()->getName() !== "koth" && !PartyProvider::hasParty($player->getName()) && !DuelsManager::isInDuel($player->getName())) {

                if(in_array($player->getLevel()->getName(), ["SumoE", "GappleE", "NodebuffE"])) return;

                if (isset(PlayerManager::$fighter[$player->getName()])) {
                    if ($damager->getName() !== PlayerManager::$fighter[$player->getName()]) {
                        $event->setCancelled();
                        $damager->sendPopup("§c» {$player->getName()} is still in combat with " . PlayerManager::$fighter[$player->getName()] . " for " . PlayerManager::$combat_time[$player->getName()] . " second(s).");
                    }
                }

                if (isset(PlayerManager::$fighter[$damager->getName()])) {
                    if ($player->getName() !== PlayerManager::$fighter[$damager->getName()]) {
                        $event->setCancelled();
                        $damager->sendPopup("§c» You're still in combat with " . PlayerManager::$fighter[$damager->getName()] . " for " . PlayerManager::$combat_time[$damager->getName()] . " second(s).");
                    }
                }

                if (!isset(PlayerManager::$fighter[$player->getName()])) {
                    if ($event->isCancelled()) return;
                    PlayerManager::$fighter[$player->getName()] = $damager->getName();
                }

                if (!isset(PlayerManager::$fighter[$damager->getName()])) {
                    if ($event->isCancelled()) return;
                    PlayerManager::$fighter[$damager->getName()] = $player->getName();
                }

                if (!isset(PlayerManager::$combat_time[$player->getName()])) {
                    if ($event->isCancelled()) return;
                    Main::getInstance()->getScheduler()->scheduleRepeatingTask(new CombatTask($player), 20);
                }

                if (!isset(PlayerManager::$combat_time[$damager->getName()])) {
                    if ($event->isCancelled()) return;
                    Main::getInstance()->getScheduler()->scheduleRepeatingTask(new CombatTask($damager), 20);
                }

                if (!$event->isCancelled()) {
                    PlayerManager::$combat_time[$player->getName()] = 15;
                    PlayerManager::$combat_time[$damager->getName()] = 15;
                }
            }elseif($player->getLevel()->getName() == "build" or $player->getLevel()->getName() == "pitchout" or $player->getLevel()->getName() == "koth")
            {
                if (!isset(PlayerManager::$combat_time[$player->getName()]))
                {
                    if ($event->isCancelled()) return;
                    Main::getInstance()->getScheduler()->scheduleRepeatingTask(new CombatTask($player), 20);
                }

                if (!isset(PlayerManager::$combat_time[$damager->getName()]))
                {
                    if ($event->isCancelled()) return;
                    Main::getInstance()->getScheduler()->scheduleRepeatingTask(new CombatTask($damager), 20);
                }

                if (!$event->isCancelled())
                {
                    PlayerManager::$combat_time[$player->getName()] = 15;
                    PlayerManager::$combat_time[$damager->getName()] = 15;
                }
            }
        }
    }

    public static function criticalHit(Player $player, Player $entity, $number = 1)
    {
        for ($i = 1; $i <= $number; $i++)
        {
            $pk = new AnimatePacket();
            $pk->action = AnimatePacket::ACTION_CRITICAL_HIT;
            $pk->entityRuntimeId = $entity->getId();
            $player->dataPacket($pk);
        }
    }

    public static function freezePlayer(Player $player, Player $damager)
    {
        switch (PlayerManager::$frozen[$player->getName()]) {
            case false:
                $damager->sendMessage("§a» {$player->getName()} is now frozen.");
                PlayerManager::$frozen[$player->getName()] = true;
                break;
            case true:
                $damager->sendMessage("§a» {$player->getName()} is now unfrozen.");
                PlayerManager::$frozen[$player->getName()] = false;
                break;
        }
    }

    public static function sendPing(Player $player, Player $damager, $event)
    {
        $damager->sendMessage("§a» {$player->getName()}'s ping is {$player->getPing()}ms.");
        $event->setCancelled();
    }
}

class CombatTask extends Task
{
    private $player;

    public function __construct($player)
    {
        $this->player = $player;
    }

    public function onRun(int $currentTick)
    {
        if($this->player->isOnline())
        {
            if(isset(PlayerManager::$combat_time[$this->player->getName()]))
            {
                if(PlayerManager::$combat_time[$this->player->getName()] == 0)
                {
                    $this->stopTask();
                }else{
                    PlayerManager::$combat_time[$this->player->getName()]--;
                }
            }else{
                $this->stopTask();
            }
        }else{
            $this->stopTask();
        }
    }

    public function stopTask()
    {
        if (isset(PlayerManager::$fighter[$this->player->getName()])) unset(PlayerManager::$fighter[$this->player->getName()]);
        if (isset(PlayerManager::$combat_time[$this->player->getName()])) unset(PlayerManager::$combat_time[$this->player->getName()]);
        Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
    }
}