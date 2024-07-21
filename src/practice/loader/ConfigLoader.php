<?php

namespace practice\loader;

use practice\Main;
use practice\manager\KnockbackManager;
use pocketmine\utils\Config;

class ConfigLoader
{
    public static function loadRessources()
    {
        ConfigLoader::loadFile();
        ConfigLoader::setDefaultValue();
    }

    public static function loadFile()
    {
        foreach (Main::getInstance()->getResources() as $resource) {
            if (!is_file(Main::getInstance()->getDataFolder() . $resource->getFilename())) Main::getInstance()->saveResource($resource->getFilename());
        }
    }

    public static function setDefaultValue()
    {
        $config = new Config(Main::getInstance()->getDataFolder() . "server.yml");
        KnockbackManager::$vertical_knockback = $config->get("vertical_knockback");
        KnockbackManager::$horizontal_knockback = $config->get("horizontal_knockback");

        if (!is_dir(Main::getInstance()->getDataFolder()."players")) @mkdir(Main::getInstance()->getDataFolder()."players");
        $event_config = new Config(Main::getInstance()->getDataFolder() . "event.json", Config::JSON);
        $event_config->setAll([]);
        $event_config->save();
    }

}