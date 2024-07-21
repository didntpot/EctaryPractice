<?php

namespace practice\api;

use practice\manager\SQLManager;

class PlayerDataAPI
{
    //Pas de bug ici
    public static function setKillDeathStreak(string $killer_name, string $killer_kill, int $killer_streak, string $victim_name, int $victim_death)
    {
        $config_killer = SQLManager::getPlayerCache($killer_name);
        if (is_null($config_killer)) {
            $config_killer["killer_streak"] = SQLManager::DEFAULT["kill_streak"];
            $config_killer["kill"] = SQLManager::DEFAULT["kill"];
        }
        $config_killer["kill_streak"] = $killer_streak;
        $config_killer["kill"] = $killer_kill;
        SQLManager::setPlayerCache($killer_name, $config_killer);
        $config_victim = SQLManager::getPlayerCache($victim_name);
        $config_victim["death"] = $victim_death;
        SQLManager::setPlayerCache($victim_name, $config_victim);

        SyncAPI::syncPlayersDB($killer_name, "kill_death_streak", ["name" => $victim_name, "cache" => SQLManager::getPlayerCache($victim_name)]);
    }

    //Jamais eu ici
    public static function getPermissions(string $name): array
    {
        $config = SQLManager::getPlayerCache($name);
        if (!isset($config["permissions"])) return [];
        return explode(",", $config["permissions"]);
    }

    //Jamais eu ici
    public static function getStringPermissions(string $name): string
    {
        $config = SQLManager::getPlayerCache($name);
        if (is_null($config)) return "";
        return $config["permissions"];
    }

    //A voir
    public static function getArraySetting($setting)
    {
        if (empty($setting)) return [];
        $config = explode(",", $setting);
        $final = [];
        foreach ($config as $value) {
            $im = explode(":", $value);
            $final[str_replace(":", "", $im[0])] = $im[1];
        }
        return $final;
    }

    //Fix 07/02/2021
    public static function getStringSetting($settings)
    {
        if (is_array($settings) or is_object($settings))
        {
            $final = "";
            if (empty($settings)) return SQLManager::DEFAULT["setting"];
            foreach ($settings as $name => $setting) {
                $final .= "$name:$setting,";
            }
            $final = substr($final, 0, -1);
            return $final;
        }else{
            return SQLManager::DEFAULT["setting"];
        }
    }

    //Pas toucher
    public static function setSetting($name, $setting, $value)
    {
        $config = SQLManager::getPlayerCache($name);
        if (is_null($config)) return;
        if ($config["setting"][$setting] === "$value") return;
        $config["setting"][$setting] = (string)"$value";
        SQLManager::setPlayerCache($name, $config);
        SyncAPI::syncPlayerDB($name, "setting");
    }

    //A voir si il a pas de bug
    public static function getSetting($name, $setting)
    {
        $config = SQLManager::getPlayerCache($name);
        if (is_null($config) or !isset($config["setting"])) $config["setting"] = self::getArraySetting(SQLManager::DEFAULT["setting"]);
        if (!isset($config["setting"][$setting])) return self::getArraySetting(SQLManager::DEFAULT["setting"])[$setting];
        $setting = $config["setting"][$setting];
        return $setting;
    }

}