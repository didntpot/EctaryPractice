<?php

namespace practice\party;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use practice\manager\PlayerManager;

class PartyProvider
{
    public static array $party = [];
    public static array $invite = [];
    public static int $min_player = 2;

    CONST MAX_SLOT = ["Basic" => 4, "Regular" => 6, "Member" => 7, "Veteran" => 8, "Myth" => 10, "Legend" => 20, "Mod" => 10, "SeniorMod" => 10, "HeadMod" => 10, "Admin" => 10, "SeniorAdmin" => 10, "HeadAdmin" => 10, "Owner" => 10, "other" => 4];

    public static function createParty(?Player $leader, $mode)
    {
        if (!PartyProvider::hasParty($leader->getName())) {
            $party = new PartySystem();
            $party->setLeader($leader);
            $party->setMode($mode);
            $party->setWorldName($mode);
            $party->start_time = time();
            $party->setMaxSlotGroup(PartyProvider::getMaxSlotGroup(PlayerManager::getInformation($leader->getName(), "group")));
            if (PartyProvider::setParty($party->getLeader(), $party)) {
                $leader->sendMessage("§a» The party has been created (" . $mode . ").");
            } else {
                $leader->sendMessage("§c» An error occurred");
            }
            return $party;
        } else {
            $leader->sendMessage("§c» You've already created a party.");
            return null;
        }
    }

    public static function getPublicPartys($number = false): ?array
    {
        $public = [];
        $public_number = 0;
        foreach (PartyProvider::$party as $name => $info) {

            if ($info->getType() === "public") {
                if (!isset($public[$info->getId()]))
                {
                    if ($number == true)
                    {
                        $public[] = $info;
                    }else{
                        $public[$info->getId()] = $info;
                    }

                    $public_number++;
                }
            }
        }
        if (empty($public)) return null;
        return [$public, $public_number];
    }

    public static function setParty(string $name, PartySystem $party)
    {
        if (!isset(PartyProvider::$party[$name])) {
            PartyProvider::$party[$name] = $party;
            return true;
        }
        return false;
    }

    public static function getMaxSlotGroup($group): int
    {
        return (isset(self::MAX_SLOT[$group])) ? self::MAX_SLOT[$group] : self::MAX_SLOT["other"];
    }

    public static function getParty(string $name): ?PartySystem
    {
        if (!isset(PartyProvider::$party[$name])) return null;
        return PartyProvider::$party[$name];
    }

    public static function hasParty($name)
    {
        if (!isset(PartyProvider::$party[$name])) return false;
        return true;
    }

    public static function deleteParty(string $name)
    {
        if (PartyProvider::hasParty($name)) unset(PartyProvider::$party[$name]);
    }

    public static function deleteAllWorld()
    {
        foreach (self::$party as $id => $value) {
            $value = get_object_vars($value);
            if ($value["world_generate"] == true) {
                $party = PartyProvider::getParty($value["leader"]);
                if (!is_null($party))
                    Server::getInstance()->getLogger()->info("The world with id ". $party->getId() ." has been deleted. (". $party->getType().")");
                $party->deleteWorld();
            }
        }
    }

    public static function removePlayerFromParty(Player $player)
    {
        $party = PartyProvider::getParty($player->getName());
        if (!is_null($party)) {
            if ($party->getLeader() === $player->getName()) {
                $party->stop();
            } else {
                $party->removeMember($player->getName());
            }
        }
    }

    public static function getInviteCount(string $name): int
    {
        return (!isset(PartyProvider::$invite[$name])) ? 0 : count(PartyProvider::$invite[$name]);
    }

    public static function hasInvite(string $name, $id): bool
    {
        if (!isset(PartyProvider::$invite[$name]) or empty(PartyProvider::$invite[$name])) return false;
        foreach (PartyProvider::$invite as $player_name => $value) {
            if ($player_name === $name) {
                foreach ($value as $id_2 => $item) {
                    if ($item["party_id"] === $id) return true;
                }
            }
        }
        return false;
    }

    public static function getInvite(string $name)
    {
        if (!isset(PartyProvider::$invite[$name])) return [];
        return PartyProvider::$invite[$name];
    }

    public static function getWorldPatch(string $id, string $world_name_from = "default"): array
    {
        if (!is_dir(str_replace('\\', "/", Server::getInstance()->getDataPath() . "worlds\\PartyMap"))) @mkdir(str_replace('\\', "/", Server::getInstance()->getDataPath() . "worlds\\PartyMap"));
        $patch_from = Server::getInstance()->getDataPath() . "worlds\\PartyMap\\$world_name_from";
        $patch_to = Server::getInstance()->getDataPath() . "worlds\\$id";
        $patch_dir = Server::getInstance()->getDataPath() . "worlds\\PartyMap\\";
        return [str_replace('\\', "/", $patch_to), str_replace('\\', "/", $patch_from), str_replace('\\', "/", $patch_dir)];
    }

    public static function getPatchWorld()
    {
        if (!is_dir(str_replace('\\', "/", Server::getInstance()->getDataPath() . "worlds\\PartyMap"))) @mkdir(str_replace('\\', "/", Server::getInstance()->getDataPath() . "worlds\\PartyMap"));
        return str_replace('\\', "/", Server::getInstance()->getDataPath() . "worlds\\PartyMap\\");
    }

    public static function deleteOldLevel()
    {
        $patch = str_replace("\\", "/", Server::getInstance()->getDataPath() . "worlds\\PartyMap\\worlds.yml");
        if (!is_file($patch)) return;
        $config = new Config($patch, Config::YAML);
        $old = $config->getAll();

        if (empty($old)) return;
        foreach ($old as $level) {
            $patch = str_replace("\\", "/", Server::getInstance()->getDataPath() . "worlds\\$level");
            if (is_dir($patch)) {
                self::delete($patch);
                Server::getInstance()->getLogger()->warning("The world with name '". $level ."' has been deleted.");
            }
        }
        $config->setAll([]);
        $config->save();

    }

    public static function getWorld(): array
    {
        $dir = scandir(PartyProvider::getPatchWorld());
        $world_name = [];
        foreach ($dir as $id => $item) {
            if ($item === "." or $item === "..") {
            } else {
                if (is_dir(PartyProvider::getPatchWorld() . $item)) {
                    $patch = PartyProvider::getPatchWorld() . "$item/party.yml";
                    if (is_file($patch)) {
                        $info = new Config($patch, Config::YAML);
                        if ($info->exists("type")) {
                            array_push($world_name, ["name" => $item, "type" => $info->get("type")]);
                        } else {
                            array_push($world_name, ["name" => $item, "type" => "Unknown"]);
                        }
                    } else {
                        array_push($world_name, ["name" => $item, "type" => "Unknown"]);
                    }
                }
            }
        }
        return $world_name;
    }

    public static function delete(string $directory): void
    {
        if (is_dir($directory)) {
            $objects = scandir($directory);

            foreach ($objects as $object) {
                if ($object !== "." and $object !== "..") {
                    if (is_dir($directory . "/" . $object)) {
                        self::delete($directory . "/" . $object);
                    } else {
                        unlink($directory . "/" . $object);
                    }
                }
            }

            rmdir($directory);
        }
    }
}