<?php

namespace practice\provider;

use pocketmine\Server;

class WorldProvider
{

    public static function countWorld($name)
    {
        $level = Server::getInstance()->getLevelByName($name);
        if (is_null($level)) return 0;
        return count($level->getPlayers());
    }

    public static function custom_copy($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        foreach (scandir($src) as $file) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    self::custom_copy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    public static function getWorldPatch(string $id, string $world_name_from = "default")
    {
        if (!is_dir(Server::getInstance()->getDataPath() . "worlds\\PartyMap")) @mkdir(Server::getInstance()->getDataPath() . "worlds\\PartyMap");
        $patch_from = Server::getInstance()->getDataPath() . "worlds\\PartyMap\\$world_name_from";
        $patch_to = Server::getInstance()->getDataPath() . "worlds\\$id";
        return [str_replace('\\', "/", $patch_to), str_replace('\\', "/", $patch_from)];
    }

    public function deleteWorld()
    {
        $patch = Server::getInstance()->getDataPath() . "worlds\\" . $this->getId();
        Server::getInstance()->unloadLevel(Server::getInstance()->getLevelByName($this->getId()));
        $this->delete($patch);
    }
}