<?php


namespace practice\manager;


use practice\api\CpsAPI;
use practice\duels\DuelsProvider;
use practice\events\listener\CustomDeath;
use practice\events\listener\PlayerInteract;
use practice\forms\ReportForm;
use practice\forms\TagsForm;
use practice\party\PartyProvider;

class InstanceManager
{

    public static function unsetAll($player)
    {
        if (isset(SQLmanager::$cache[$player])) unset(SQLmanager::$cache[$player]);

        if (isset(CpsAPI::$clicksData[strtolower($player)])) unset(CpsAPI::$clicksData[strtolower($player)]);
        if (isset(DuelsProvider::$duel_spectate[$player])) unset(DuelsProvider::$duel_spectate[$player]);
        if (isset(CustomDeath::$damager[$player])) unset(CustomDeath::$damager[$player]);
        if (isset(PlayerInteract::$cooldown[$player])) unset(PlayerInteract::$cooldown[$player]);
        if (isset(PlayerInteract::$soupChestCooldown[$player])) unset(PlayerInteract::$soupChestCooldown[$player]);

        if (isset(PlayerManager::$reach[$player])) unset(PlayerManager::$reach[$player]);
        if (isset(PlayerManager::$combo[$player])) unset(PlayerManager::$combo[$player]);
        if (isset(PlayerManager::$staff_mode[$player])) unset(PlayerManager::$staff_mode[$player]);
        if (isset(PlayerManager::$staff_chat[$player])) unset(PlayerManager::$staff_chat[$player]);
        if (isset(PlayerManager::$frozen[$player])) unset(PlayerManager::$frozen[$player]);
        if (isset(PlayerManager::$os[$player])) unset(PlayerManager::$os[$player]);
        if (isset(PlayerManager::$ip[$player])) unset(PlayerManager::$ip[$player]);
        if (isset(PlayerManager::$id_device[$player])) unset(PlayerManager::$id_device[$player]);
        if (isset(PlayerManager::$nickname[$player])) unset(PlayerManager::$nickname[$player]);
        if (isset(PlayerManager::$sync_status[$player])) unset(PlayerManager::$sync_status[$player]);
        if (isset(PlayerManager::$combat_time[$player])) unset(PlayerManager::$combat_time[$player]);
        if (isset(PlayerManager::$need_pearl[$player])) unset(PlayerManager::$need_pearl[$player]);
        if (isset(PlayerManager::$pearl_time[$player])) unset(PlayerManager::$pearl_time[$player]);
        if (isset(PlayerManager::$pots[$player])) unset(PlayerManager::$pots[$player]);
        if (isset(PlayerManager::$uuid[$player])) unset(PlayerManager::$uuid[$player]);

        if (isset(ReportForm::$playerList[$player])) unset(ReportForm::$playerList[$player]);
        if (isset(TagsForm::$cooldown[$player])) unset(TagsForm::$cooldown[$player]);
        if (isset(ChatManager::$after_chat[$player])) unset(ChatManager::$after_chat[$player]);
        if (isset(EntityManager::$fishing[$player])) unset(EntityManager::$fishing[$player]);
        if (isset(PermissionInterface::$attachments[$player])) unset(PermissionInterface::$attachments[$player]);

        if (isset(PartyProvider::$party[$player])) unset(PartyProvider::$party[$player]);
        if (isset(PartyProvider::$invite[$player])) unset(PartyProvider::$invite[$player]);
        if (isset(PartyProvider::$invite[$player])) unset(PartyProvider::$invite[$player]);
    }
}