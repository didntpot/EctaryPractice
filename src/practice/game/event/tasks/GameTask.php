<?php

namespace practice\game\event\tasks;

use pocketmine\scheduler\Task;
use practice\game\event\Manager;
use practice\Main;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\level\Position;
use pocketmine\level\Location;

class GameTask extends Task
{
    public static $playerOne = null;
    public static $playerTwo = null;

    public static $playerOneName = null;
    public static $playerTwoName = null;

    public static $inmatch = false;

    public function onRun(int $currentTick)
    {
        if (Manager::$is_started === true) {
            #task ok
            if (self::$inmatch === false) {
                self::$inmatch = true;

                $config = new Config(Main::getInstance()->getDataFolder() . "event.json", Config::JSON);
                $all = $config->getAll();

                foreach ($all as $name => $value) {
                    if (count($all) == 1) return;
                }
                        $players = array_rand($all, 2);
                        var_dump($players);
                        $eplayer = Server::getInstance()->getPlayer($name);


                        $eplayer->sendMessage("§a» {$players[0]} vs {$players[1]}");

                        $playerOne = Server::getInstance()->getPlayer($players[0]);
                        $playerTwo = Server::getInstance()->getPlayer($players[1]);

                        self::$playerOne = $playerOne;
                        self::$playerTwo = $playerTwo;
                        self::$playerOneName = $playerOne->getName();
                        self::$playerTwoName = $playerTwo->getName();

                        switch (Manager::$event_mode) {
                            case "nodebuff":
                                $playerOne->teleport(new Location(305.5000, 29, 319.5000, 0, 0, Server::getInstance()->getLevelByName("NodebuffE")));
                                $playerTwo->teleport(new Location(305.5000, 29, 291.5000, 0, 0, Server::getInstance()->getLevelByName("NodebuffE")));
                                break;
                            case "gapple":
                                $playerOne->teleport(new Location(305.5000, 29, 319.5000, 0, 0, Server::getInstance()->getLevelByName("GappleE")));
                                $playerTwo->teleport(new Location(305.5000, 29, 291.5000, 0, 0, Server::getInstance()->getLevelByName("GappleE")));
                                break;
                            case "sumo":
                                $playerOne->teleport(new Location(205.5000, 28, 211.5000, 0, 0, Server::getInstance()->getLevelByName("SumoE")));
                                $playerTwo->teleport(new Location(205.5000, 28, 201.5000, 0, 0, Server::getInstance()->getLevelByName("SumoE")));
                                break;
                        }
                        $playerOne->setImmobile();
                        $playerTwo->setImmobile();
                Main::getInstance()->getScheduler()->scheduleRepeatingTask(new MatchStartTask(self::$playerOne, self::$playerTwo), 20);
                Main::getInstance()->getScheduler()->scheduleRepeatingTask(new DeathTask(self::$playerOne, self::$playerTwo, self::$playerOneName, self::$playerTwoName), 20);
                self::$playerOne = null;
                self::$playerTwo = null;
                self::$playerOneName = null;
                self::$playerTwoName = null;
            }
        } else {
            Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
        }
    }
}