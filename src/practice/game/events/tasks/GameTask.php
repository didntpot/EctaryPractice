<?php

namespace practice\game\events\tasks;

use practice\events\listener\PlayerJoin;
use practice\Main;
use practice\game\events\EventManager;
use pocketmine\scheduler\Task;
use pocketmine\level\Location;
use pocketmine\Server;
use practice\api\KitsAPI;
use pocketmine\item\Item;
use practice\scoreboard\{EventScoreboard};

class GameTask extends Task
{
    public function onRun(int $currentTick)
    {
        if(EventManager::$isStarted == true)
        {
            if(EventManager::$isWaiting == true)
            {
                if(EventManager::$playerCount < 2) return EventManager::sendPopup("§cWaiting for more players...");

                if(EventManager::$playerCount == 0) return EventManager::stop();

                if(EventManager::$waitingTime == 0)
                {

                    EventManager::$isWaiting = false;
                    EventManager::sendMessage("§a» The event has started!");

                    switch(EventManager::$eventType)
                    {
                        case "nodebuff":
                            foreach(Server::getInstance()->getLevelByName(EventManager::NODEBUFF_LEVEL)->getPlayers() as $eplayer)
                            {
                                unset(PlayerJoin::$scoreboard[$eplayer->getName()]);
                                PlayerJoin::$scoreboard[$eplayer->getName()] = new EventScoreboard($eplayer);
                                PlayerJoin::$scoreboard[$eplayer->getName()]->sendRemoveObjectivePacket();
                                EventScoreboard::createLines($eplayer);
                            }
                            break;
                        case "gapple":
                            foreach(Server::getInstance()->getLevelByName(EventManager::GAPPLE_LEVEL)->getPlayers() as $eplayer)
                            {
                                unset(PlayerJoin::$scoreboard[$eplayer->getName()]);
                                PlayerJoin::$scoreboard[$eplayer->getName()] = new EventScoreboard($eplayer);
                                PlayerJoin::$scoreboard[$eplayer->getName()]->sendRemoveObjectivePacket();
                                EventScoreboard::createLines($eplayer);
                            }
                            break;
                        case "sumo":
                            foreach(Server::getInstance()->getLevelByName(EventManager::SUMO_LEVEL)->getPlayers() as $eplayer)
                            {
                                unset(PlayerJoin::$scoreboard[$eplayer->getName()]);
                                PlayerJoin::$scoreboard[$eplayer->getName()] = new EventScoreboard($eplayer);
                                PlayerJoin::$scoreboard[$eplayer->getName()]->sendRemoveObjectivePacket();
                                EventScoreboard::createLines($eplayer);
                            }
                            break;
                    }

                }elseif(EventManager::$waitingTime == 15)
                {

                    EventManager::sendMessage("§a» The event is starting in 15 seconds.");
                    EventManager::$waitingTime--;

                }elseif(EventManager::$waitingTime == 5)
                {

                    EventManager::sendMessage("§a» The event is starting in 5 seconds.");
                    EventManager::$waitingTime--;

                }else{
                    EventManager::$waitingTime--;
                }
            }else{
                if(EventManager::$inMatch == false)
                {
                    if(EventManager::$playerCount == 1)
                    {
                        foreach(EventManager::$players as $playerName => $value)
                        {
                            Server::getInstance()->broadcastMessage("§a» $playerName has won the event!");
                        }

                        switch(EventManager::$eventType)
                        {
                            case "nodebuff":
                                foreach(Server::getInstance()->getLevelByName(EventManager::NODEBUFF_LEVEL)->getPlayers() as $eplayer)
                                {
                                    $eplayer->teleport(Server::getInstance()->getLevelByName("spawn")->getSafeSpawn());
                                    KitsAPI::addLobbyKit($eplayer);
                                }
                                break;
                            case "gapple":
                                foreach(Server::getInstance()->getLevelByName(EventManager::GAPPLE_LEVEL)->getPlayers() as $eplayer)
                                {
                                    $eplayer->teleport(Server::getInstance()->getLevelByName("spawn")->getSafeSpawn());
                                    KitsAPI::addLobbyKit($eplayer);
                                }
                                break;
                            case "sumo":
                                foreach(Server::getInstance()->getLevelByName(EventManager::SUMO_LEVEL)->getPlayers() as $eplayer)
                                {
                                    $eplayer->teleport(Server::getInstance()->getLevelByName("spawn")->getSafeSpawn());
                                    KitsAPI::addLobbyKit($eplayer);
                                }
                                break;
                        }

                        EventManager::stop();
                    }else{
                        if(EventManager::$playerCount == 1)
                        {
                            foreach(EventManager::$players as $playerName => $value)
                            {
                                Server::getInstance()->broadcastMessage("§a» $playerName has won the event!");
                            }

                            switch(EventManager::$eventType)
                            {
                                case "nodebuff":
                                    foreach(Server::getInstance()->getLevelByName(EventManager::NODEBUFF_LEVEL)->getPlayers() as $eplayer)
                                    {
                                        $eplayer->teleport(Server::getInstance()->getLevelByName("spawn")->getSafeSpawn());
                                        KitsAPI::addLobbyKit($eplayer);
                                    }
                                    break;
                                case "gapple":
                                    foreach(Server::getInstance()->getLevelByName(EventManager::GAPPLE_LEVEL)->getPlayers() as $eplayer)
                                    {
                                        $eplayer->teleport(Server::getInstance()->getLevelByName("spawn")->getSafeSpawn());
                                        KitsAPI::addLobbyKit($eplayer);
                                    }
                                    break;
                                case "sumo":
                                    foreach(Server::getInstance()->getLevelByName(EventManager::SUMO_LEVEL)->getPlayers() as $eplayer)
                                    {
                                        $eplayer->teleport(Server::getInstance()->getLevelByName("spawn")->getSafeSpawn());
                                        KitsAPI::addLobbyKit($eplayer);
                                    }
                                    break;
                            }

                            return;
                        }
                        $players = array_rand(EventManager::$players, 2);

                        EventManager::$playerOne = $players[0];
                        EventManager::$playerTwo = $players[1];

                        $playerOne = Server::getInstance()->getPlayer($players[0]);
                        $playerTwo = Server::getInstance()->getPlayer($players[1]);

                        EventManager::sendMessage("§a» {$playerOne->getName()} vs {$playerTwo->getName()}");

                        EventManager::$roundCount = EventManager::$roundCount+1;

                        if(!is_null($playerOne))
                        {
                            switch(EventManager::$eventType)
                            {
                                case "nodebuff":
                                    $playerOne->setImmobile();
                                    $playerOne->teleport(new Location(305.5000, 29, 319.5000, 0, 0, Server::getInstance()->getLevelByName("NodebuffE")));
                                    Main::getInstance()->getScheduler()->scheduleRepeatingTask(new MatchStart($playerOne), 20);
                                    break;
                                case "gapple":
                                    $playerOne->setImmobile();
                                    $playerOne->teleport(new Location(305.5000, 29, 319.5000, 0, 0, Server::getInstance()->getLevelByName("GappleE")));
                                    Main::getInstance()->getScheduler()->scheduleRepeatingTask(new MatchStart($playerOne), 20);
                                    break;
                                case "sumo":
                                    $playerOne->setImmobile();
                                    $playerOne->teleport(new Location(205.5000, 28, 211.5000, 0, 0, Server::getInstance()->getLevelByName("SumoE")));
                                    Main::getInstance()->getScheduler()->scheduleRepeatingTask(new MatchStart($playerOne), 20);
                                    break;
                            }

                        }else{
                            unset(EventManager::$players[$players[1]]);

                            EventManager::sendMessage("§a» $players[0] won the match against $players[1].");
                            $playerZero = Server::getInstance()->getPlayer($players[1]);

                            EventManager::$inMatch = false;
                        }

                        if(!is_null($playerTwo))
                        {
                            switch(EventManager::$eventType)
                            {
                                case "nodebuff":
                                    $playerTwo->setImmobile();
                                    $playerTwo->teleport(new Location(305.5000, 29, 291.5000, 0, 0, Server::getInstance()->getLevelByName("NodebuffE")));
                                    Main::getInstance()->getScheduler()->scheduleRepeatingTask(new MatchStart($playerTwo), 20);
                                    break;
                                case "gapple":
                                    $playerTwo->setImmobile();
                                    $playerTwo->teleport(new Location(305.5000, 29, 291.5000, 0, 0, Server::getInstance()->getLevelByName("GappleE")));
                                    Main::getInstance()->getScheduler()->scheduleRepeatingTask(new MatchStart($playerTwo), 20);
                                    break;
                                case "sumo":
                                    $playerTwo->setImmobile();
                                    $playerTwo->teleport(new Location(205.5000, 28, 201.5000, 0, 0, Server::getInstance()->getLevelByName("SumoE")));
                                    Main::getInstance()->getScheduler()->scheduleRepeatingTask(new MatchStart($playerTwo), 20);
                                    break;
                            }
                        }else{
                            unset(EventManager::$players[$players[0]]);

                            EventManager::sendMessage("§a» $players[1] won the match against $players[0].");

                            EventManager::$inMatch = false;
                        }

                        EventManager::$inMatch = true;
                    }
                }else{
                    $playerOne = Server::getInstance()->getPlayer(EventManager::$playerOne);
                    $playerTwo = Server::getInstance()->getPlayer(EventManager::$playerTwo);

                    if(!is_null($playerOne) && !is_null($playerTwo))
                    {
                        if($playerOne->getY() < 19)
                        {
                            unset(EventManager::$players[$playerOne->getName()]);
                            EventManager::remove($playerOne->getName());
                            EventManager::$inMatch = false;
                            EventManager::sendMessage("§a» {$playerTwo->getName()} won the match against {$playerOne->getName()}.");
                            $playerOne->getInventory()->clearAll();
                            $playerOne->getArmorInventory()->clearAll();
                            $playerOne->removeAllEffects();
                            $playerTwo->getInventory()->clearAll();
                            $playerTwo->getArmorInventory()->clearAll();
                            $playerTwo->removeAllEffects();

                            switch(EventManager::$eventType)
                            {
                                case "nodebuff":
                                    $playerOne->teleport(Server::getInstance()->getLevelByName("NodebuffE")->getSafeSpawn());
                                    $playerTwo->teleport(Server::getInstance()->getLevelByName("NodebuffE")->getSafeSpawn());
                                    break;
                                case "gapple":
                                    $playerOne->teleport(Server::getInstance()->getLevelByName("GappleE")->getSafeSpawn());
                                    $playerTwo->teleport(Server::getInstance()->getLevelByName("GappleE")->getSafeSpawn());
                                    break;
                                case "sumo":
                                    $playerOne->teleport(Server::getInstance()->getLevelByName("SumoE")->getSafeSpawn());
                                    $playerTwo->teleport(Server::getInstance()->getLevelByName("SumoE")->getSafeSpawn());
                                    break;
                            }
                        }

                        if($playerTwo->getY() < 19)
                        {
                            unset(EventManager::$players[$playerTwo->getName()]);
                            EventManager::remove($playerTwo->getName());
                            EventManager::$inMatch = false;
                            EventManager::sendMessage("§a» {$playerOne->getName()} won the match against {$playerTwo->getName()}.");
                            $playerOne->getInventory()->clearAll();
                            $playerOne->getArmorInventory()->clearAll();
                            $playerOne->removeAllEffects();
                            $playerTwo->getInventory()->clearAll();
                            $playerTwo->getArmorInventory()->clearAll();
                            $playerTwo->removeAllEffects();

                            switch(EventManager::$eventType)
                            {
                                case "nodebuff":
                                    $playerOne->teleport(Server::getInstance()->getLevelByName("NodebuffE")->getSafeSpawn());
                                    $playerTwo->teleport(Server::getInstance()->getLevelByName("NodebuffE")->getSafeSpawn());
                                    break;
                                case "gapple":
                                    $playerOne->teleport(Server::getInstance()->getLevelByName("GappleE")->getSafeSpawn());
                                    $playerTwo->teleport(Server::getInstance()->getLevelByName("GappleE")->getSafeSpawn());
                                    break;
                                case "sumo":
                                    $playerOne->teleport(Server::getInstance()->getLevelByName("SumoE")->getSafeSpawn());
                                    $playerTwo->teleport(Server::getInstance()->getLevelByName("SumoE")->getSafeSpawn());
                                    break;
                            }
                        }
                    }else{
                        if(is_null($playerOne) && !is_null($playerTwo))
                        {
                            switch(EventManager::$eventType)
                            {
                                case "nodebuff":
                                    $playerTwo->teleport(Server::getInstance()->getLevelByName("NodebuffE")->getSafeSpawn());
                                    $playerTwo->getInventory()->clearAll();
                                    $playerTwo->getArmorInventory()->clearAll();
                                    $playerTwo->removeAllEffects();
                                    #EventManager::remove(EventManager::$playerOne);
                                    EventManager::$inMatch = false;
                                    break;
                                case "gapple":
                                    $playerTwo->teleport(Server::getInstance()->getLevelByName("GappleE")->getSafeSpawn());
                                    $playerTwo->getInventory()->clearAll();
                                    $playerTwo->getArmorInventory()->clearAll();
                                    $playerTwo->removeAllEffects();
                                    #EventManager::remove(EventManager::$playerOne);
                                    EventManager::$inMatch = false;
                                    break;
                                case "sumo":
                                    $playerTwo->teleport(Server::getInstance()->getLevelByName("SumoE")->getSafeSpawn());
                                    $playerTwo->getInventory()->clearAll();
                                    $playerTwo->getArmorInventory()->clearAll();
                                    $playerTwo->removeAllEffects();
                                    #EventManager::remove(EventManager::$playerOne);
                                    EventManager::$inMatch = false;
                                    break;
                            }
                        }

                        if(is_null($playerTwo) && !is_null($playerOne))
                        {
                            switch(EventManager::$eventType)
                            {
                                case "nodebuff":
                                    $playerOne->teleport(Server::getInstance()->getLevelByName("NodebuffE")->getSafeSpawn());
                                    $playerOne->getInventory()->clearAll();
                                    $playerOne->getArmorInventory()->clearAll();
                                    $playerOne->removeAllEffects();
                                    #EventManager::remove(EventManager::$playerTwo);
                                    EventManager::$inMatch = false;
                                    break;
                                case "gapple":
                                    $playerOne->teleport(Server::getInstance()->getLevelByName("GappleE")->getSafeSpawn());
                                    $playerOne->getInventory()->clearAll();
                                    $playerOne->getArmorInventory()->clearAll();
                                    $playerOne->removeAllEffects();
                                    #EventManager::remove(EventManager::$playerOne);
                                    EventManager::$inMatch = false;
                                    break;
                                case "sumo":
                                    $playerOne->teleport(Server::getInstance()->getLevelByName("SumoE")->getSafeSpawn());
                                    $playerOne->getInventory()->clearAll();
                                    $playerOne->getArmorInventory()->clearAll();
                                    $playerOne->removeAllEffects();
                                    #EventManager::remove(EventManager::$playerOne);
                                    EventManager::$inMatch = false;
                                    break;
                            }
                        }
                    }
                }
            }
        }else{
            if(EventManager::$waitingTime !== 120)
            {
                EventManager::$waitingTime = 120;
            }
        }
    }
}

class MatchStart extends Task
{
    private $time = 3;

    public function __construct($player)
    {
        $this->player = $player;
    }

    public function onRun(int $currentTick)
    {
        if(!is_null($this->player))
        {
            $this->player->addTitle("§b{$this->time}");

            if($this->time == 0)
            {
                $this->player->setImmobile(false);

                switch(EventManager::$eventType)
                {
                    case "nodebuff":
                        KitsAPI::addEventNodebuffKit($this->player);
                        break;
                    case "gapple":
                        KitsAPI::addGappleKit($this->player);
                        break;
                    case "sumo":
                        KitsAPI::addSumoKit($this->player);
                        break;
                }

                Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());

            }else{
                $this->player->setImmobile(true);
                $this->time--;
            }

        }else{
            Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
        }
    }
}