<?php


namespace practice\manager;


class ServerStatsManager
{

    public static $connection = [];

    public static function getPlatformsCount()
    {
        $caches = SQLManager::$cache;
        $platforms = [];

        foreach ($caches as $name => $info) {
            $device = $info["platform_device"];
            if (!isset($platforms[$device])) {
                $platforms[$device] = 1;
            } else {
                $platforms[$device]++;
            }
        }
        return $platforms;
    }

    public static function getJoinNumber()
    {
        if (!isset(ServerStatsManager::$connection["1hour"])) {
            $onehour = 0;
        } else {
            $onehour = ServerStatsManager::$connection["1hour"]["con"];
        }
        if (!isset(ServerStatsManager::$connection["2hour"])) {
            $twohour = 0;
        } else {
            $twohour = ServerStatsManager::$connection["2hour"]["con"];
        }

        return ["1hour" => $onehour, "2hour" => $twohour];
    }

    public static function addJoinNumber()
    {
        if (!isset(ServerStatsManager::$connection["1hour"])) ServerStatsManager::$connection["1hour"] = ["time" => time(), "con" => 0];

        $diff = time() - ServerStatsManager::$connection["1hour"]["time"];
        if ($diff <= 3600) {
            ServerStatsManager::$connection["1hour"]["con"]++;
        } elseif ($diff <= 7200) {
            if (!isset(ServerStatsManager::$connection["2hour"])) ServerStatsManager::$connection["2hour"] = ["time" => time(), "con" => 0];
            ServerStatsManager::$connection["2hour"]["con"]++;
        } else {
            ServerStatsManager::$connection["1hour"]["con"] = 1;
            ServerStatsManager::$connection["2hour"]["con"] = 0;
        }
    }
}