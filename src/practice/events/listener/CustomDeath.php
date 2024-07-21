<?php

namespace practice\events\listener;

use practice\duels\manager\DuelsManager;
use practice\manager\LevelManager;
use practice\manager\PlayerManager;
use practice\party\PartyProvider;
use practice\api\{
    KitsAPI,
    PlayerDataAPI,
    LightningAPI
};
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\entity\projectile\EnderPearl;

class CustomDeath implements Listener
{
    public static $damager = [];

    public function onHeal(EntityRegainHealthEvent $event)
    {
        if($event->getEntity() instanceof Player and $event->getRegainReason() === EntityRegainHealthEvent::CAUSE_SATURATION)
        {
            $event->setCancelled();
        }
    }

    public function onEntityDamageByEntity(EntityDamageByEntityEvent $event)
    {
        $entity = $event->getEntity();
        $damager = $event->getDamager();

        if ($event->isCancelled()) return;
        if ($entity instanceof Player) {
            if ($damager instanceof Player) {
                if($damager->getName() !== $entity->getName())
                {
                    CustomDeath::$damager[$entity->getName()] = $damager->getName();
                }
            }
        }
    }

    public function onEntityDamage(EntityDamageEvent $event)
    {
        $player = $event->getEntity();
        if ($player instanceof Player) {

            if ($event->getCause() === EntityDamageEvent::CAUSE_PROJECTILE)
            {
                if(in_array($player->getLevel()->getName(), ["nodebuff", "Nodebuff"])) $event->setKnockback(0.100);
            }elseif ($event->getCause() === EntityDamageEvent::CAUSE_FALL)
            {
                $event->setCancelled();
            }elseif ($event->getCause() === EntityDamageEvent::CAUSE_VOID)
            {
                if(!DuelsManager::isInDuel($player->getName()))
                {
                    if(isset(CustomDeath::$damager[$player->getName()]))
                    {
                        $killer = Server::getInstance()->getPlayer(CustomDeath::$damager[$player->getName()]);
                        if (!is_null($killer)) {
                            $event->setCancelled();
                            CustomDeath::addStats($player, $killer);
                            CustomDeath::reKitPlayer($player, $killer);
                            CustomDeath::unsetAllCache($player, $killer);
                            if(PlayerDataAPI::getSetting($killer->getName(), "lightning_death") === "true")
                            {
                                LightningAPI::spawnLightning($player->getPosition(), $killer);
                            }
                            LevelManager::teleportSpawn($player);
                        } else {
                            $event->setCancelled();
                            LevelManager::teleportSpawn($player);
                        }
                    }else{
                        $event->setCancelled();
                        LevelManager::teleportSpawn($player);
                    }
                }
            }

            if (CustomDeath::isCanceled($player)) return;
            if ($event->getFinalDamage() >= $player->getHealth() && $event->getCause() !== EntityDamageEvent::CAUSE_FALL) {
                if (isset(CustomDeath::$damager[$player->getName()])) {
                    $killer = Server::getInstance()->getPlayer(CustomDeath::$damager[$player->getName()]);
                    if (!is_null($killer)) {
                        $event->setCancelled();
                        CustomDeath::addStats($player, $killer);
                        CustomDeath::reKitPlayer($player, $killer);
                        CustomDeath::unsetAllCache($player, $killer);
                        if(PlayerDataAPI::getSetting($killer->getName(), "lightning_death") === "true")
                        {
                            LightningAPI::spawnLightning($player->getPosition(), $killer);
                        }
                        LevelManager::teleportSpawn($player);
                    } else {
                        $event->setCancelled();
                        LevelManager::teleportSpawn($player);
                    }
                } else {
                    LevelManager::teleportSpawn($player);
                }
            }
        }
    }

    public static function addStats(Player $player, Player $killer)
    {
        $killer_name = $killer->getName();
        $player_name = $player->getName();

        if (in_array($player->getLevel()->getFolderName(), LevelManager::LEVEL_NO_STATS)) return;
        if (!PartyProvider::hasParty($killer_name)) {
            $str = PlayerManager::getInformation($killer_name, "kill_streak") + 1;
            $killer->sendMessage("§a» Your kill-streak is now: $str");

            if (!PartyProvider::hasParty($player->getName()))
            {
                PlayerManager::setInformation($player->getName(), "kill_streak", 0);
                $player->sendMessage("§c» You've lost your kill-streak.");
            }
        }

        if (!PartyProvider::hasParty($player_name) and !PartyProvider::hasParty($killer_name)){
            PlayerDataAPI::setKillDeathStreak($killer_name,
                PlayerManager::getInformation($killer_name, "kill") + 1,
                PlayerManager::getInformation($killer_name, "kill_streak") + 1,
                $player->getName(),
                PlayerManager::getInformation($player->getName(), "death") + 1);
        }
    }

    public static function isCanceled(Player $player): bool
    {
        if (DuelsManager::isInDuel($player->getName())) return true;
        if (in_array($player->getLevel()->getName(), ["NodebuffE", "GappleE", "SumoE"])) return true;
        return false;
    }
    public static function unsetAllCache(Player $player, Player $killer)
    {
        unset(PlayerManager::$pearl_time[$player->getName()]);
        unset(PlayerManager::$combat_time[$player->getName()]);
        unset(PlayerManager::$combat_time[$killer->getName()]);
        unset(PlayerManager::$fighter[$player->getName()]);
        unset(PlayerManager::$fighter[$killer->getName()]);
        unset(CustomDeath::$damager[$killer->getName()]);
        unset(CustomDeath::$damager[$player->getName()]);
    }
    public static function reKitPlayer(Player $player, Player $killer)
    {
        $health = round($killer->getHealth(), 1);
        $player_display_name = $player->getDisplayName();
        $killer_name_display = $killer->getDisplayName();
        $killer_name = $killer->getName();
        $killer->setHealth(20);

        switch ($killer->getLevel()->getName()) {
            case "nodebuff":
                Server::getInstance()->broadcastMessage("§7{$killer_name_display} killed {$player_display_name} §c[" . PlayerManager::getPots($killer) . " Pots - {$health} HP]");
                if (PlayerDataAPI::getSetting($killer_name, "auto_rekit") === "true") KitsAPI::addNodebuffKit($killer);
                break;
            case "debuff":
                Server::getInstance()->broadcastMessage("§7{$killer_name_display} killed {$player_display_name} §c[" . PlayerManager::getPots($killer) . " Pots - {$health} HP]");
                if (PlayerDataAPI::getSetting($killer_name, "auto_rekit") === "true") KitsAPI::addDebuffKit($killer);
                break;
            case "sumo":
                Server::getInstance()->broadcastMessage("§7{$killer_name_display} killed {$player_display_name} §c[{$health} HP]");
                if (PlayerDataAPI::getSetting($killer_name, "auto_rekit") === "true") KitsAPI::addSumoKit($killer);
                break;
            case "buhc":
                Server::getInstance()->broadcastMessage("§7{$killer_name_display} killed {$player_display_name} §c[{$health} HP]");
                if (PlayerDataAPI::getSetting($killer_name, "auto_rekit") === "true") KitsAPI::addBuildUHCKit($killer);
                break;
            case "build":
                Server::getInstance()->broadcastMessage("§7{$killer_name_display} killed {$player_display_name} §c[{$health} HP]");
                if (PlayerDataAPI::getSetting($killer_name, "auto_rekit") === "true") KitsAPI::addBuildKit($killer);
                break;
            case "combo":
                Server::getInstance()->broadcastMessage("§7{$killer_name_display} killed {$player_display_name} §c[{$health} HP]");
                if (PlayerDataAPI::getSetting($killer_name, "auto_rekit") === "true") KitsAPI::addComboKit($killer);
                break;
            case "gapple":
                Server::getInstance()->broadcastMessage("§7{$killer_name_display} killed {$player_display_name} §c[{$health} HP]");
                if (PlayerDataAPI::getSetting($killer_name, "auto_rekit") === "true") KitsAPI::addGappleKit($killer);
                break;
            case "soup":
                Server::getInstance()->broadcastMessage("§7{$killer_name_display} killed {$player_display_name} §c[{$health} HP]");
                if (PlayerDataAPI::getSetting($killer_name, "auto_rekit") === "true") KitsAPI::addSoupRefillKit($killer);
                break;
            case "hcf":
                Server::getInstance()->broadcastMessage("§7{$killer_name_display} killed {$player_display_name} §c[" . PlayerManager::getPots($killer) . " Pots - {$health} HP]");
                if (PlayerDataAPI::getSetting($killer_name, "auto_rekit") === "true") KitsAPI::addHCFKit($killer);
                break;
            case "classic":
                Server::getInstance()->broadcastMessage("§7{$killer_name_display} killed {$player_display_name} §c[{$health} HP]");
                if (PlayerDataAPI::getSetting($killer_name, "auto_rekit") === "true") KitsAPI::addClassicKit($killer);
                break;
            case "axe":
                Server::getInstance()->broadcastMessage("§7{$killer_name_display} killed {$player_display_name} §c[{$health} HP]");
                if (PlayerDataAPI::getSetting($killer_name, "auto_rekit") === "true") KitsAPI::addAxeKit($killer);
                break;
            case "koth":
                Server::getInstance()->broadcastMessage("§7{$killer_name_display} killed {$player_display_name} §c[{$health} HP]");
                if (PlayerDataAPI::getSetting($killer_name, "auto_rekit") === "true") KitsAPI::addKothKit($killer);
                break;
            case "fist1":
                Server::getInstance()->broadcastMessage("§7{$killer_name_display} killed {$player_display_name} §c[{$health} HP]");
                if (PlayerDataAPI::getSetting($killer_name, "auto_rekit") === "true") KitsAPI::addFistKit($killer);
                break;
            case "fist2":
                Server::getInstance()->broadcastMessage("§7{$killer_name_display} killed {$player_display_name} §c[{$health} HP]");
                if (PlayerDataAPI::getSetting($killer_name, "auto_rekit") === "true") KitsAPI::addFistKit($killer);
                break;
            case "pitchout":
                Server::getInstance()->broadcastMessage("§7{$killer_name_display} killed {$player_display_name} §c[{$health} HP]");
                if (PlayerDataAPI::getSetting($killer_name, "auto_rekit") === "true") KitsAPI::addPitchoutKit($killer);
                break;
            default:
                Server::getInstance()->broadcastMessage("§7{$killer_name_display} killed {$player_display_name} §c[" . PlayerManager::getPots($killer) . " Pots - {$health} HP]");
                break;
        }
    }
}