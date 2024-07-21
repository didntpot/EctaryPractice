<?php


namespace practice\manager;


use pocketmine\Server;
use practice\Main;
use practice\provider\WorkerProvider;
use practice\tasks\async\SyncCapeLocal;

class CapesManager
{
    public static $cape = [];

    public static function initTableCapes($db)
    {
        $prep_groups = $db->prepare("CREATE TABLE IF NOT EXISTS `EctaryS4`.`capes`(
                                              `id` INT NOT NULL AUTO_INCREMENT,
                                              `cape_bytes` BLOB NOT NULL,
                                              `cape_name` VARCHAR(40) NOT NULL,
                                              `cape_image_link` TEXT NOT NULL,
                                              `permission` VARCHAR(40) NOT NULL,
                                              PRIMARY KEY(`id`)
                                            ) ENGINE = InnoDB");

        if (empty($db->error)) {
            $startTime = microtime(true);
            $prep_groups->execute();
            $endTime = microtime(true);
            Main::getInstance()->getLogger()->info("The capes table has been initialized. (" . round(($endTime - $startTime) * 1000, 2) . "ms)");
        } else {
            Server::getInstance()->getLogger()->warning("[Practice] " . $db->error);
        }
    }

    public static function setCacheCape(array $capes)
    {
        CapesManager::$cape = $capes;
    }

    public static function getAllCape()
    {
        if (empty(CapesManager::$cape)) return null;
        return CapesManager::$cape;
    }

    public static function getAllCapeName(): array
    {
        $tags = CapesManager::$cape;
        $tag_name = [];
        foreach ($tags as $tag) {
            $tag_name[] = $tag["cape_name"];
        }
        return $tag_name;
    }

    public static function initCacheCape()
    {
        SQLManager::sendToWorker(new SyncCapeLocal(), WorkerProvider::SYSTEME_ASYNC);
    }

    public static function getCapeByName($name)
    {
        $capes = CapesManager::$cape;

        foreach ($capes as $id => $cape) {
            if ($cape["cape_name"] === $name) {
                return $cape;
            }
        }
        return null;
    }
}