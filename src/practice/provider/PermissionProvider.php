<?php


namespace practice\provider;


use pocketmine\Server;
use practice\manager\GroupManager;
use practice\manager\PermissionInterface;

class PermissionProvider
{
    public static function implodePermission($permission): string
    {
        if (is_array($permission) and !empty($permission)) {
            $perm = implode(",", $permission);
            return $perm;
        } else {
            return "";
        }
    }

    public static function explodePermission($permission): array
    {
        if (is_string($permission) and !empty($permission)) {
            $perm = explode(",", $permission);
            return $perm;
        } else {
            return [];
        }
    }

    public static function getGroupPermission($group_name)
    {
        if (!isset(GroupManager::$group_cache[$group_name])) return [];
        $permissions = explode(",", GroupManager::$group_cache[$group_name]["permissions"]);
        return $permissions;
    }

    public static function setPlayerPermission(string $name)
    {
        $permissions = GroupManager::combinePermissionsPlayer($name);
        $player = Server::getInstance()->getPlayer($name);
        if (!is_null($player)) {
            PermissionInterface::registerPlayer($player);
            PermissionInterface::setPermission($player, $permissions);
        }
    }
}