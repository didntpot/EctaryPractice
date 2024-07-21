<?php


namespace practice\duels\events;


use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\Player;
use practice\duels\Duels;
use practice\duels\manager\DuelsManager;
use practice\events\listener\PlayerJoin;
use practice\manager\KnockbackManager;
use practice\api\LightningAPI;
use practice\api\PlayerDataAPI;
use practice\manager\PlayerManager;
use pocketmine\Server;
use pocketmine\level\Location;
use practice\api\KitsAPI;
use practice\scoreboard\{DuelEndScoreboard};

class EntityDamage implements Listener
{
    public function onEntityDamageByEntity(EntityDamageByEntityEvent $event)
    {
        $player = $event->getEntity();
        $damager = $event->getDamager();

        if ($player instanceof Player)
        {
            if($event->getCause() !== EntityDamageEvent::CAUSE_PROJECTILE)
            {
                if (DuelsManager::isInDuel($player->getName()))
                {
                    $duel = DuelsManager::getDuel($player->getName());
                    if (!is_null($duel))
                    {
                        if ($duel instanceof Duels)
                        {
                            if ($duel->getPvpEnable() == false)
                            {
                                $event->setCancelled();
                                return;
                            }

                            $event->setAttackCooldown($duel->getAttackDelay());

                            if($duel->getKit() == "boxing")
                            {
                                if(!$event->isCancelled())
                                {
                                    if(isset(PlayerManager::$boxingHits[$damager->getName()]))
                                    {
                                        PlayerManager::$boxingHits[$damager->getName()]++;

                                        if(PlayerManager::$boxingHits[$damager->getName()] == 100)
                                        {
                                            $duel->addDeath($player, $damager);
                                            $player->getInventory()->clearAll();
                                            $player->getArmorInventory()->clearAll();
                                            $damager->getInventory()->clearAll();
                                            $damager->getArmorInventory()->clearAll();

                                            PlayerManager::$boxingHits[$damager->getName()] = 0;
                                            PlayerManager::$boxingHits[$player->getName()] = 0;
                                        }
                                    }
                                }
                            }

                            if(!$event->isCancelled())
                            {
                                if(isset(PlayerManager::$duelHits[$damager->getName()]))
                                {
                                    PlayerManager::$duelHits[$damager->getName()]++;
                                }

                                PlayerManager::$lastOpoName[$player->getName()] = $damager->getName();
                            }
                        }

                        if($duel->getStatus() == 4)
                        {
                            $event->setCancelled();
                        }
                    }
                }
            }
        }
    }

    public function onEntityDDDD(EntityDamageEvent $event)
    {
        $player = $event->getEntity();
        $damager = null;

        if ($player instanceof Player) {
            if (DuelsManager::isInDuel($player->getName()))
            {
                $duel = DuelsManager::getDuel($player->getName());
                if(!is_null($duel))
                {
                    if($duel instanceof Duels)
                    {
                            if($event->getFinalDamage() >= $player->getHealth() && $event->getCause() !== EntityDamageEvent::CAUSE_FALL)
                            {
                                if($duel->getKit() == "thebridge" or $duel->getKit() == "hikabrain")
                                {
                                    if(isset(PlayerManager::$playerTeam[$player->getName()]))
                                    {
                                        $event->setCancelled();
                                        switch(PlayerManager::$playerTeam[$player->getName()])
                                        {
                                            case "red":
                                                if($duel->getKit() == "thebridge")
                                                {
                                                    $player->teleport(new Location(549.5000, 74, 1801.5000, 256.5000, 1.4, $player->getLevel()));
                                                    KitsAPI::addTheBridgeKit($player);
                                                }

                                                if($duel->getKit() == "hikabrain")
                                                {
                                                    $player->teleport(new Location(295.5000, 54, 269.5000, 90.5000, 87.4, $player->getLevel()));
                                                    KitsAPI::addHikabrainKit($player);
                                                }

                                                $player->setHealth(20);
                                                break;

                                            case "blue":
                                                if($duel->getKit() == "thebridge")
                                                {
                                                    $player->teleport(new Location(549.5000, 74, 1859.5000, 179.5000, 3.5, $player->getLevel()));
                                                    KitsAPI::addTheBridgeKit($player);
                                                }

                                                if($duel->getKit() == "hikabrain")
                                                {
                                                    $player->teleport(new Location(255.5000, 54, 269.5000, 269.5000, 1.5, $player->getLevel()));
                                                    KitsAPI::addHikabrainKit($player);
                                                }


                                                $player->setHealth(20);
                                                break;
                                        }
                                        $event->setCancelled();
                                    }
                                }else{
                                    if($event instanceof EntityDamageByEntityEvent)
                                    {
                                        $d = $event->getDamager();

                                        if ($d instanceof Player)
                                        {

                                            if(!$duel->getStatus() != 4)
                                            {
                                                $damager = $d;

                                                if(!$event->isCancelled())
                                                {
                                                    PlayerManager::$opoInventory[$player->getName()] = [$damager->getInventory()->getContents()];
                                                    PlayerManager::$opoInventory[$damager->getName()] = [$player->getInventory()->getContents()];

                                                    PlayerManager::$lastOpoHealth[$player->getName()] = round($damager->getHealth(), 1);
                                                    PlayerManager::$lastOpoHealth[$damager->getName()] = round($player->getHealth(), 1);


                                                    if(PlayerDataAPI::getSetting($damager->getName(), "lightning_death") === "true")
                                                    {
                                                        LightningAPI::spawnLightning($player->getPosition(), $damager);
                                                    }

                                                    $player->getInventory()->clearAll();
                                                    $player->getArmorInventory()->clearAll();

                                                }
                                                $event->setCancelled();

                                            }
                                            $player->getInventory()->clearAll();
                                            $player->getArmorInventory()->clearAll();
                                            $event->setCancelled();
                                            $duel->setStatus(4);
                                            $duel->addDeath($player, $damager);

                                            unset(PlayerJoin::$scoreboard[$player->getName()]);
                                            PlayerJoin::$scoreboard[$player->getName()] = new DuelEndScoreboard($player);
                                            PlayerJoin::$scoreboard[$player->getName()]->sendRemoveObjectivePacket();
                                            DuelEndScoreboard::createLines($player);

                                            unset(PlayerJoin::$scoreboard[$damager->getName()]);
                                            PlayerJoin::$scoreboard[$damager->getName()] = new DuelEndScoreboard($damager);
                                            PlayerJoin::$scoreboard[$damager->getName()]->sendRemoveObjectivePacket();
                                            DuelEndScoreboard::createLines($damager);
                                        }
                                    }else{
                                        $event->setCancelled();
                                        $duel->setStatus(4);
                                        $duel->addDeath($player);
                                        $player->getInventory()->clearAll();
                                        $player->getArmorInventory()->clearAll();
                                    }
                                }

                            }else{
                                if ($duel->getStatus() != 3) $event->setCancelled();
                            }
                    }
                }
            }
        }
    }
}