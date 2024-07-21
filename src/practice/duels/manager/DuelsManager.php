<?php

namespace practice\duels\manager;

use pocketmine\level\Position;
use pocketmine\Server;
use pocketmine\utils\Config;
use practice\duels\Duels;
use practice\duels\DuelsProvider;
use practice\duels\Task\Delete;
use practice\events\listener\PlayerJoin;
use practice\scoreboard\{DuelStartScoreboard};

class DuelsManager
{

    public static function createDuel(array $players, $duel_config)
    {
        $map = array_rand($duel_config["world"]);

        $duel = new Duels($players);
        $duel->setName($duel_config["name"]);
        $duel->setType($duel_config["type"]);
        $duel->setWaitTime(3);
        $duel->setWaitFinishTime(2);
        $duel->setDuelTime(1200);
        $duel->setSpawnPositions($duel_config["location"][$map]);
        $duel->setKit($duel_config["kit"]);
        $duel->setMap($duel_config["world"][$map]);
        $duel->setKB($duel_config["kb"]);
        $duel->setAttackDelay($duel_config["attack_delay"]);
        $duel->setPvpEnable($duel_config["pvp"]);
        $duel->setTeaming($duel_config["teaming"]);
        $duel->setPlayerNumberInTeam($duel_config["player_number_in_team"]);
        $duel->start();

        DuelsProvider::$duels[$duel->getId()] = $duel;
    }

    public static function createCustomDuel(array $players, $spawn_position, $game_name = "Duel game", $type = "unranked", $wait_time = 3, $wait_finish = 2, $duel_time = 600, $kit = "nodebuff", $map = "football_duel", $kb = 0.15, $at = 1, $pvp = true)
    {
        $duel = new Duels($players);
        $duel->setName($game_name);
        $duel->setType($type);
        $duel->setWaitTime($wait_time);
        $duel->setWaitFinishTime($wait_finish);
        $duel->setDuelTime($duel_time);
        $duel->setSpawnPositions($spawn_position);
        $duel->setKit($kit);
        $duel->setMap($map);
        $duel->setKB($kb);
        $duel->setAttackDelay($at);
        $duel->setPvpEnable($pvp);
        $duel->start();

        DuelsProvider::$duels[$duel->getId()] = $duel;
    }

    public static function isInDuel($player)
    {
        if (!is_null(self::getDuel($player))) return true;
        return false;
    }

    public static function getDuel($player): ?Duels
    {
        if (empty(DuelsProvider::$duels)) return null;
        foreach (DuelsProvider::$duels as $id => $duel)
        {
            if ($duel instanceof Duels)
            {
                if (in_array($player, $duel->getPlayers()) or in_array($player, $duel->getDeadplayers()) or in_array($player, $duel->getSpectatorPlayers())) return $duel;
            }
        }
        return null;
    }

    public static function deleteDuel($id): bool
    {
        if (empty(DuelsProvider::$duels)) return false;
        foreach (DuelsProvider::$duels as $id => $duel)
        {
            if ($duel instanceof Duels)
            {
                if ($duel->getId() === $id)
                {
                    $duel->stop();
                }
            }
        }
    }

    public static function deleteOldLevel()
    {
        if (is_dir(Server::getInstance()->getDataPath().DuelsProvider::WORLD_PATCH))
        {
            $config = new Config(str_replace("\\", "/", Server::getInstance()->getDataPath().DuelsProvider::WORLD_PATCH."/id.json"));
            $all = $config->getAll();

            if (!empty($all))
            {
                foreach ($all as $level => $item)
                {
                    $patch = str_replace("\\", "/", Server::getInstance()->getDataPath()."worlds/$level");
                    if (is_dir($patch))
                    {
                        Delete::delete($patch);
                        Server::getInstance()->getLogger()->info("[Practice] $level duels level deleted...");
                    }
                }
            }

            $config->setAll([]);
            $config->save();
        }
    }

    public static function getAllDuel()
    {
        return DuelsProvider::$duels;
    }

    public static function getAllDuelById()
    {
        $d = [];
        foreach (DuelsProvider::$duels as $duel)
        {
            $d[] = $duel;
        }
        return $d;
    }


    public static function getLastInvitation($player)
    {
        if (isset(DuelsProvider::$duels_invitation[$player]))
        {
            return DuelsProvider::$duels_invitation[$player];
        }else{
            return null;
        }
    }

    public static function addInvitation($player, $sender, $map): bool
    {
       if(!isset(DuelsProvider::$duels_invitation[$player]) or !DuelsProvider::$duels_invitation[$player] === (string) $player)
        {
            DuelsProvider::$duels_invitation[$player] = ['player' => $sender, 'config_map' => $map];
            return true;
        }else{
           return false;
       }
    }

    public static function removeInvitation($player)
    {
        if (isset(DuelsProvider::$duels_invitation[$player])) unset(DuelsProvider::$duels_invitation[$player]);
        foreach (DuelsProvider::$duels_invitation as $id => $value)
        {
            if ($value["player"] === $player)
            {
                unset(DuelsProvider::$duels_invitation[$id]);
                return true;
            }
        }
        return false;
    }
}