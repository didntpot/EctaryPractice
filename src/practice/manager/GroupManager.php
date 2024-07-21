<?php


namespace practice\manager;


use pocketmine\permission\PermissionManager;
use pocketmine\Player;
use pocketmine\Server;
use practice\api\PlayerDataAPI;
use practice\Main;
use practice\provider\PermissionProvider;
use practice\provider\WorkerProvider;
use practice\tasks\async\AsyncGetGroup;
use practice\tasks\async\SyncPermissionReload;

class GroupManager
{

    public static $player_permission_interface;
    public static $group_cache = [];

    public static function initTableGroup($db)
    {
        $prep_groups = $db->prepare("CREATE TABLE IF NOT EXISTS `EctaryS4`.`groups`(
                              `id` INT NOT NULL AUTO_INCREMENT,
                              `group_name` TEXT NOT NULL,
                              `permissions` TEXT NOT NULL,
                              `syntax` TEXT NULL,
                              PRIMARY KEY(`id`)
                            ) ENGINE = InnoDB;");

        if (empty($db->error)) {
            $startTime = microtime(true);
            $prep_groups->execute();
            $endTime = microtime(true);
            Main::getInstance()->getLogger()->info("The group table has been initialized. (" . round(($endTime - $startTime) * 1000, 2) . "ms)");
        }
    }

    public static function initGroupCache()
    {
        SQLManager::sendToWorker(new AsyncGetGroup(), WorkerProvider::SYSTEME_ASYNC);
    }

    public static function setGroupCache(array $cache)
    {
        GroupManager::$group_cache = $cache;
    }

    public static function getGroupCache()
    {
        return GroupManager::$group_cache;
    }

    public static function getAllGroupName(): array
    {
        $groups = GroupManager::$group_cache;
        $groups_name = [];
        foreach ($groups as $name => $perm) {
            $groups_name[] = $name;
        }
        return $groups_name;
    }

    public static function getPlayersGroup($group_name)
    {
        $players = [];
        if (!empty(Server::getInstance()->getOnlinePlayers())) {
            foreach (SQLManager::$cache as $player => $info) {
                if ($info["group"] === (string)$group_name) $players[$player] = $info;
            }
        }
        return $players;
    }

    public static function combinePermissionsPlayer($player_name)
    {
        $group = PlayerManager::getInformation($player_name, "group");
        $player_permissions = PlayerDataAPI::getPermissions($player_name);
        $group_permissions = PermissionProvider::getGroupPermission($group);
        return array_merge($group_permissions, $player_permissions);
    }

    public static function reloadPlayerPermission($player)
    {
        SQLManager::sendToWorker(new SyncPermissionReload($player), WorkerProvider::COMMAND_ASYNC);
    }

    public static function getPermissionInterface(): PermissionInterface
    {
        return GroupManager::$player_permission_interface;
    }

    public static function initPermissionInterface()
    {
        $interface = new PermissionInterface(Main::getInstance());
        GroupManager::$player_permission_interface = $interface;
    }
}

class PermissionInterface
{

    public static array $attachments = [];

    public static $main;

    public function __construct(Main $main)
    {
        PermissionInterface::$main = $main;
    }

    public static function registerPlayer(Player $player)
    {
        $attachment = $player->addAttachment(PermissionInterface::$main);
        PermissionInterface::$attachments[$player->getName()] = $attachment;
    }

    public static function getAttachement(Player $player)
    {
        if (!isset(PermissionInterface::$attachments[$player->getName()])) return null;
        return PermissionInterface::$attachments[$player->getName()];
    }


    public static function setPermission(Player $player, array $permissions)
    {
        $perm = [];
        foreach ($permissions as $permission) {
            $perm[$permission] = true;
        }

        $attachment = PermissionInterface::getAttachement($player);
        if (!is_null($attachment)) {
            $attachment->clearPermissions();
            $attachment->setPermissions($perm);
        }
    }
}