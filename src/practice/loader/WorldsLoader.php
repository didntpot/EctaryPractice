<?php

namespace practice\loader;

use pocketmine\Server;

class WorldsLoader
{
    public static function initWorlds()
    {
        $worlds = [
            "nodebuff",
            "sumo",
            "build",
            "combo",
            "gapple",
            "NodebuffE",
            "GappleE",
            "SumoE",
            "koth",
            "fist1",
            "fist2",
            "kitroom", "pitchout", "buhc", "soup"];

        foreach ($worlds as $world) {
            Server::getInstance()->loadLevel($world);
        }
    }
}