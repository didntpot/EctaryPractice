<?php

namespace practice\game\event\tasks;

use pocketmine\scheduler\Task;
use practice\Main;
use practice\game\event\Manager;
use practice\api\KitsAPI;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\level\Position;
use practice\manager\PlayerManager;

class DeathTask extends Task
{
    public function __construct($playerOne, $playerTwo, $playerOneName, $playerTwoName)
    {
        $this->playerOne = $playerOne;
        $this->playerTwo = $playerTwo;
        $this->playerOneName = $playerOneName;
        $this->playerTwoName = $playerTwoName;
    }

    public $finished;

    public function onRun(int $currentTick)
    {
        if (Manager::$is_running === true) {
            $players = [$this->playerOne, $this->playerTwo];
            $config = new Config(Main::getInstance()->getDataFolder() . "event.json", Config::JSON);
            #$all = $config->getAll();

            if (!$players[0]->isOnline() or !isset(Manager::$is_ingame[$players[0]->getName()])) {
                var_dump("player one is null");
                Manager::removeFromGame($this->playerOneName);
                $config->remove($this->playerOneName);
                $config->save();
                if (Manager::$player_count === 1) {
                    foreach (Server::getInstance()->getLevelByName("NodebuffE")->getPlayers() as $eventplayers) {
                        $eventplayers->teleport(Server::getInstance()->getLevelByName("spawn")->getSafeSpawn());
                        KitsAPI::addLobbyKit($eventplayers);
                    }
                    foreach (Server::getInstance()->getLevelByName("GappleE")->getPlayers() as $eventplayers) {
                        $eventplayers->teleport(Server::getInstance()->getLevelByName("spawn")->getSafeSpawn());
                        KitsAPI::addLobbyKit($eventplayers);
                    }
                    foreach (Server::getInstance()->getLevelByName("SumoE")->getPlayers() as $eventplayers) {
                        $eventplayers->teleport(Server::getInstance()->getLevelByName("spawn")->getSafeSpawn());
                        KitsAPI::addLobbyKit($eventplayers);
                    }
                    Server::getInstance()->broadcastMessage("§a» {$players[1]->getDisplayName()} won the event!");
                    $this->finished = true;
                    $players[1]->removeAllEffects();
                    Manager::$player_count = 0;
                    Manager::setGame(false);
                    $config->setAll([]);
                    $config->save();
                    Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
                } else {
                    switch (Manager::$event_mode) {
                        case "nodebuff":
                            $players[1]->teleport(new Position(306, 31, 276, Server::getInstance()->getLevelByName("NodebuffE")));
                            unset(PlayerManager::$combat_time[$players[1]->getName()]);
                            break;
                        case "gapple":
                            $players[1]->teleport(new Position(306, 31, 276, Server::getInstance()->getLevelByName("GappleE")));
                            unset(PlayerManager::$combat_time[$players[1]->getName()]);
                            break;
                        case "sumo":
                            $players[1]->teleport(new Position(205, 28, 176, Server::getInstance()->getLevelByName("SumoE")));
                            unset(PlayerManager::$combat_time[$players[1]->getName()]);
                            break;
                    }
                    $players[1]->removeAllEffects();
                    $players[1]->getInventory()->clearAll();
                    $players[1]->getArmorInventory()->clearAll();
                    Main::getInstance()->getScheduler()->scheduleRepeatingTask(new RematchScheduler(), 20);
                    Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
                }
            } else {
                if ($players[0]->getY() < -1) {
                    Server::getInstance()->broadcastMessage("§a» {$players[1]->getDisplayName()} won the match against {$players[0]->getDisplayName()}!");
                    Manager::removeFromGame($players[0]->getName());
                    $config->remove($players[0]->getName());
                    $config->save();
                    switch (Manager::$event_mode) {
                        case "nodebuff":
                            $players[1]->teleport(new Position(306, 31, 276, Server::getInstance()->getLevelByName("NodebuffE")));
                            $players[0]->teleport(new Position(306, 31, 276, Server::getInstance()->getLevelByName("NodebuffE")));
                            break;
                        case "gapple":
                            $players[1]->teleport(new Position(306, 31, 276, Server::getInstance()->getLevelByName("GappleE")));
                            $players[0]->teleport(new Position(306, 31, 276, Server::getInstance()->getLevelByName("GappleE")));
                            break;
                        case "sumo":
                            $players[1]->teleport(new Position(205, 28, 176, Server::getInstance()->getLevelByName("SumoE")));
                            $players[0]->teleport(new Position(205, 28, 176, Server::getInstance()->getLevelByName("SumoE")));
                            break;
                    }
                    $players[1]->removeAllEffects();
                    $players[0]->removeAllEffects();
                    $players[1]->getInventory()->clearAll();
                    $players[1]->getArmorInventory()->clearAll();
                    $players[0]->getInventory()->clearAll();
                    $players[0]->getArmorInventory()->clearAll();
                    if (Manager::$player_count === 1) {
                        foreach (Server::getInstance()->getLevelByName("NodebuffE")->getPlayers() as $eventplayers) {
                            $eventplayers->teleport(Server::getInstance()->getLevelByName("spawn")->getSafeSpawn());
                            KitsAPI::addLobbyKit($eventplayers);
                        }
                        foreach (Server::getInstance()->getLevelByName("GappleE")->getPlayers() as $eventplayers) {
                            $eventplayers->teleport(Server::getInstance()->getLevelByName("spawn")->getSafeSpawn());
                            KitsAPI::addLobbyKit($eventplayers);
                        }
                        foreach (Server::getInstance()->getLevelByName("SumoE")->getPlayers() as $eventplayers) {
                            $eventplayers->teleport(Server::getInstance()->getLevelByName("spawn")->getSafeSpawn());
                            KitsAPI::addLobbyKit($eventplayers);
                        }
                        Server::getInstance()->broadcastMessage("§a» {$players[1]->getName()} won the event!");
                        Manager::$player_count = 0;
                        Manager::setGame(false);
                        $config->setAll([]);
                        $config->save();
                        Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
                    } else {
                        Main::getInstance()->getScheduler()->scheduleRepeatingTask(new RematchScheduler(), 20);
                        Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
                    }
                }
            }

            if (!$players[1]->isOnline() or !isset(Manager::$is_ingame[$players[1]->getName()])) {
                var_dump("player two is null");
                Manager::removeFromGame($this->playerTwoName);
                $config->remove($this->playerTwoName);
                $config->save();
                if (Manager::$player_count === 1) {
                    foreach (Server::getInstance()->getLevelByName("NodebuffE")->getPlayers() as $eventplayers) {
                        $eventplayers->teleport(Server::getInstance()->getLevelByName("spawn")->getSafeSpawn());
                        KitsAPI::addLobbyKit($eventplayers);
                    }
                    foreach (Server::getInstance()->getLevelByName("GappleE")->getPlayers() as $eventplayers) {
                        $eventplayers->teleport(Server::getInstance()->getLevelByName("spawn")->getSafeSpawn());
                        KitsAPI::addLobbyKit($eventplayers);
                    }
                    foreach (Server::getInstance()->getLevelByName("SumoE")->getPlayers() as $eventplayers) {
                        $eventplayers->teleport(Server::getInstance()->getLevelByName("spawn")->getSafeSpawn());
                        KitsAPI::addLobbyKit($eventplayers);
                    }
                    if ($this->finished === true) {
                        Server::getInstance()->broadcastMessage("§a» {$players[0]->getDisplayName()} won the event!");
                    }
                    $players[0]->removeAllEffects();
                    Manager::$player_count = 0;
                    Manager::setGame(false);
                    $config->setAll([]);
                    $config->save();
                    Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
                } else {
                    switch (Manager::$event_mode) {
                        case "nodebuff":
                            $players[0]->teleport(new Position(306, 31, 276, Server::getInstance()->getLevelByName("NodebuffE")));
                            unset(PlayerManager::$combat_time[$players[0]->getName()]);
                            break;
                        case "gapple":
                            $players[0]->teleport(new Position(306, 31, 276, Server::getInstance()->getLevelByName("GappleE")));
                            unset(PlayerManager::$combat_time[$players[0]->getName()]);
                            break;
                        case "sumo":
                            $players[0]->teleport(new Position(205, 28, 176, Server::getInstance()->getLevelByName("SumoE")));
                            unset(PlayerManager::$combat_time[$players[0]->getName()]);
                            break;
                    }
                    $players[0]->removeAllEffects();
                    $players[0]->getInventory()->clearAll();
                    $players[0]->getArmorInventory()->clearAll();
                    Main::getInstance()->getScheduler()->scheduleRepeatingTask(new RematchScheduler(), 20);
                    Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
                }
            } else {
                if ($players[1]->getY() < -1) {
                    Server::getInstance()->broadcastMessage("§a» {$players[0]->getDisplayName()} won the match against {$players[1]->getDisplayName()}!");
                    Manager::removeFromGame($players[1]->getName());
                    $config->remove($players[1]->getName());
                    $config->save();
                    switch (Manager::$event_mode) {
                        case "nodebuff":
                            $players[0]->teleport(new Position(306, 31, 276, Server::getInstance()->getLevelByName("NodebuffE")));
                            $players[1]->teleport(new Position(306, 31, 276, Server::getInstance()->getLevelByName("NodebuffE")));
                            break;
                        case "gapple":
                            $players[0]->teleport(new Position(306, 31, 276, Server::getInstance()->getLevelByName("GappleE")));
                            $players[1]->teleport(new Position(306, 31, 276, Server::getInstance()->getLevelByName("GappleE")));
                            break;
                        case "sumo":
                            $players[0]->teleport(new Position(205, 28, 176, Server::getInstance()->getLevelByName("SumoE")));
                            $players[1]->teleport(new Position(205, 28, 176, Server::getInstance()->getLevelByName("SumoE")));
                            break;
                    }
                    $players[1]->removeAllEffects();
                    $players[0]->removeAllEffects();
                    $players[0]->getInventory()->clearAll();
                    $players[0]->getArmorInventory()->clearAll();
                    $players[1]->getInventory()->clearAll();
                    $players[1]->getArmorInventory()->clearAll();
                    if (Manager::$player_count === 1) {
                        foreach (Server::getInstance()->getLevelByName("NodebuffE")->getPlayers() as $eventplayers) {
                            $eventplayers->teleport(Server::getInstance()->getLevelByName("spawn")->getSafeSpawn());
                            KitsAPI::addLobbyKit($eventplayers);
                        }
                        foreach (Server::getInstance()->getLevelByName("GappleE")->getPlayers() as $eventplayers) {
                            $eventplayers->teleport(Server::getInstance()->getLevelByName("spawn")->getSafeSpawn());
                            KitsAPI::addLobbyKit($eventplayers);
                        }
                        foreach (Server::getInstance()->getLevelByName("SumoE")->getPlayers() as $eventplayers) {
                            $eventplayers->teleport(Server::getInstance()->getLevelByName("spawn")->getSafeSpawn());
                            KitsAPI::addLobbyKit($eventplayers);
                        }
                        Server::getInstance()->broadcastMessage("§a» {$players[0]->getDisplayName()} won the event!");
                        Manager::$player_count = 0;
                        Manager::setGame(false);
                        $config->setAll([]);
                        $config->save();
                        $players[1]->removeAllEffects();
                        $players[0]->removeAllEffects();
                        Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
                    } else {
                        Main::getInstance()->getScheduler()->scheduleRepeatingTask(new RematchScheduler(), 20);
                        Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
                    }
                }
            }
        } else {
            Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
        }
    }
}