<?php


namespace practice\manager;


use pocketmine\Server;
use practice\Main;
use practice\provider\WorkerProvider;
use practice\tasks\async\SyncTagsLocal;

class TagsManager
{
    public static $tags = [];


    public static function initTableTags($db)
    {
        $prep_groups = $db->prepare("CREATE TABLE `EctaryS4`.`tags`(
                                              `id` INT NOT NULL AUTO_INCREMENT,
                                              `tags_name` VARCHAR(40) NOT NULL,
                                              `permission` VARCHAR(40) NOT NULL,
                                              PRIMARY KEY(`id`)
                                            ) ENGINE = InnoDB");

        if (empty($db->error)) {
            $startTime = microtime(true);
            $prep_groups->execute();
            $endTime = microtime(true);
            Main::getInstance()->getLogger()->info("The tags table has been initialized. (" . round(($endTime - $startTime) * 1000, 2) . "ms)");
        } else {
            Server::getInstance()->getLogger()->warning("[Practice] " . $db->error);
        }
    }

    public static function setCacheTags(array $tags)
    {
        TagsManager::$tags = $tags;
    }

    public static function getAllTags()
    {
        if (empty(TagsManager::$tags)) return null;
        return TagsManager::$tags;
    }

    public static function getAllTagsName(): array
    {
        $tags = TagsManager::$tags;
        $tag_name = [];
        foreach ($tags as $tag) {
            $tag_name[] = $tag["tags_name"];
        }
        return $tag_name;
    }

    public static function initCacheTags()
    {
        SQLManager::sendToWorker(new SyncTagsLocal(), WorkerProvider::SYSTEME_ASYNC);
    }
}