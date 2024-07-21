<?php

namespace practice\tasks;

use practice\api\InformationAPI;
use practice\duels\manager\DuelsManager;
use practice\game\events\EventManager;
use practice\manager\TimeManager;
use practice\scoreboard\{SpawnScoreboard, FFAScoreboard};
use practice\events\listener\{PlayerJoin};
use pocketmine\scheduler\{AsyncTask};
use practice\duels\DuelQueue;
use practice\manager\PlayerManager;
use pocketmine\{Server};
use practice\api\PlayerDataAPI;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;

class ScoreboardTask extends AsyncTask
{

    public function onRun():void
    {

    }

    public function onCompletion(Server $server)
    {
        foreach(PlayerJoin::$scoreboard as $name => $value)
        {
            $player = Server::getInstance()->getPlayer($name);
            if(!$player) continue;

            $name = $player->getName();

            if(PlayerDataAPI::getSetting($name, "scoreboard") === "false")
            {
                unset(PlayerJoin::$scoreboard[$name]);
                $pk = new RemoveObjectivePacket();
                $pk->objectiveName = "{$player->getId()}";
                $player->dataPacket($pk);
            }else{
                if($player->scoreboard == "spawn")
                {
                    PlayerJoin::$scoreboard[$name]
                        ->setLine(2, " §bPlaying: §f".count(Server::getInstance()->getOnlinePlayers()))
                        ->setLine(3, " §bQueued: §f".DuelQueue::countAllQueue())
                        ->setLine(8, " §bElo: §f".PlayerManager::getInformation($player->getName(), "elo"))
                        ->setLine(9, " §bDivision: §f".PlayerManager::getInformation($player->getName(), "division"))
                        ->setLine(14, " {$player->scoreboardRound}")
                        ->set();
                    {
                    }
                }

                if($player->scoreboard == "ffa")
                {
                    $combat = 0;
                    $enemyPing = 0;

                    if(isset(PlayerManager::$combat_time[$name]))
                    {
                        $combat = PlayerManager::$combat_time[$name];
                    }else{
                        $combat = 0;
                    }


                    if(isset(PlayerManager::$fighter[$player->getName()]))
                    {
                        $fplayer = Server::getInstance()->getPlayer(PlayerManager::$fighter[$player->getName()]);

                        if(!is_null($fplayer))
                        {
                            $enemyPing = $fplayer->getPing();
                        }else{
                            $enemyPing = 0;
                        }
                    }

                    PlayerJoin::$scoreboard[$name]
                        ->setLine(2, " §aYour Ping: §f{$player->getPing()}ms")
                        ->setLine(3, " §cTheir Ping: §f{$enemyPing}ms")
                        ->setLine(4, " §4Combat: §f{$combat}s")
                        ->setLine(9, " {$player->scoreboardRound}")
                        ->set();{
                }
                }

                if($player->scoreboard == "duel_game")
                {
                    $duration = "";
                    $ping = "";
                    $oName = "";

                    $duel = DuelsManager::getDuel($name);

                    if(!is_null($duel))
                    {
                        $opo = $duel->getOpponent($name);
                        $duration = TimeManager::secondsToTime($duel->duel_duration);

                        if(!is_null($opo))
                        {
                            $opponent = Server::getInstance()->getPlayer($opo);

                            if(!is_null($opponent))
                            {
                                $opo = $opponent;

                                if($opo->getPing() > 0 && $opo->getPing() < 71)
                                {
                                    $ping = "§a{$opo->getPing()}";
                                }

                                if($opo->getPing() > 70 && $opo->getPing() < 101)
                                {
                                    $ping = "§e{$opo->getPing()}";
                                }

                                if($opo->getPing() > 100 && $opo->getPing() < 151)
                                {
                                    $ping = "§6{$opo->getPing()}";
                                }

                                if($opo->getPing() > 150 && $opo->getPing() < 201)
                                {
                                    $ping = "§c{$opo->getPing()}";
                                }

                                if($opo->getPing() > 200)
                                {
                                    $ping = "§4{$opo->getPing()}";
                                }

                                $oName = $opo->getDisplayName();
                            }
                        }
                    }

                    PlayerJoin::$scoreboard[$name]
                        ->setLine(3, " §c{$oName} §7| {$ping}ms")
                        ->setLine(5, " §bYour Ping: §f{$player->getPing()}ms")
                        ->setLine(6, " §bDuration: §f{$duration}")
                        ->setLine(8, " {$player->scoreboardRound}")
                        ->set();{
                }
                }

                if($player->scoreboard == "pre_event")
                {
                    $stamp = TimeManager::secondsToTime(EventManager::$waitingTime);

                    PlayerJoin::$scoreboard[$name]
                        ->setLine(2, " §bStarting in: §f{$stamp}")
                        ->setLine(4, " {$player->scoreboardRound}")
                        ->set();{
                }
                }

                if($player->scoreboard == "event")
                {
                    PlayerJoin::$scoreboard[$name]
                        ->setLine(2, " §aPlayers Left: §f".EventManager::$playerCount)
                        ->setLine(3, " §cEliminated: §f".EventManager::$eliminatedCount)
                        ->setLine(4, " §eRound: §f".EventManager::$roundCount)
                        ->setLine(6, " {$player->scoreboardRound}")
                        ->set();{
                }
                }
            }
        }
    }
}