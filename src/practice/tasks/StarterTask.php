<?php

namespace practice\tasks;

use pocketmine\scheduler\Task;
use practice\api\InformationAPI;
use practice\api\PlayerDataAPI;
use practice\duels\Duels;
use practice\duels\manager\DuelsManager;
use practice\Main;
use practice\tasks\async\scoreboard\{AsyncDuelScoreboard, AsyncSpawnScoreboard, AsyncFFAScoreboard, AsyncRemoveScoreboard, AsyncEventScoreboard, AsyncEditKitScoreboard, AsyncEventWaitingScoreboard};
use practice\manager\PlayerManager;
use pocketmine\Server;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use practice\duels\DuelQueue;
use practice\game\events\EventManager;
use practice\game\koth\KOTHManager;
use practice\api\KitsAPI;
use practice\manager\TimeManager;
use practice\scoreboard\{SpawnScoreboard, FFAScoreboard};
use practice\events\listener\PlayerJoin;

class StarterTask extends Task
{
    public $round = "0";

    public function onRun(int $currentTick)
    {
        Server::getInstance()->getAsyncPool()->submitTask(new ScoreboardTask());

        return;
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
           if (PlayerManager::isSync($player->getName())) continue;
            if (PlayerDataAPI::getSetting($player->getName(), "scoreboard") === "true") {
                if ($player->getLevel()->getName() === "spawn") {

                    $queued = "";

                    if(DuelQueue::isInQueue($player->getName()))
                    {
                        $queued = DuelQueue::getPlayerQueue($player->getName())["queue"]["duel"]["name"];
                    }

                    $next = "";
                    $kill = PlayerManager::getInformation($player->getName(), "kill");

                    switch(PlayerManager::getInformation($player->getName(), "division"))
                    {
                        case "§8[Bronze I]":
                            $next = "$kill/25";
                            break;
                        case "§8[Bronze II]":
                            $next = "$kill/75";
                            break;
                        case "§8[Bronze III]":
                            $next = "$kill/150";
                            break;
                        case "§7[Silver I]":
                            $next = "$kill/250";
                            break;
                        case "§7[Silver II]":
                            $next = "$kill/350";
                            break;
                        case "§7[Silver III]":
                            $next = "$kill/500";
                            break;
                        case "§e[Gold I]":
                            $next = "$kill/700";
                            break;
                        case "§e[Gold II]":
                            $next = "$kill/850";
                            break;
                        case "§e[Gold III]":
                            $next = "$kill/1000";
                            break;
                        case "§3[Platinum I]":
                            $next = "$kill/1300";
                            break;
                        case "§3[Platinum II]":
                            $next = "$kill/1600";
                            break;
                        case "§3[Platinum III]":
                            $next = "$kill/1900";
                            break;
                        case "§b[Diamond I]":
                            $next = "$kill/2400";
                            break;
                        case "§b[Diamond II]":
                            $next = "$kill/2800";
                            break;
                        case "§b[Diamond III]":
                            $next = "$kill/3000";
                            break;
                        case "§9[Challenger I]":
                            $next = "$kill/3500";
                            break;
                        case "§9[Challenger II]":
                            $next = "$kill/4000";
                            break;
                        case "§9[Challenger III]":
                            $next = "$kill/5000";
                            break;
                        case "§c[Master I]":
                            $next = "$kill/MAX";
                            break;
                    }


                    Main::getInstance()->getServer()->getAsyncPool()->submitTask(new AsyncSpawnScoreboard($player->getName(), count(Server::getInstance()->getOnlinePlayers()), InformationAPI::getServerRegion(),  DuelQueue::countAllQueue(), $queued, PlayerManager::getInformation($player->getName(), "elo"), PlayerManager::getInformation($player->getName(), "division"), $this->round, $next));

                } elseif(DuelsManager::isInDuel($player->getName())){
                    $duel = DuelsManager::getDuel($player->getName());
                    if (!is_null($duel))
                    {
                        $this->startDuel($player, $duel);
                    }else{
                        $this->startFFA($player);
                    }
                }elseif($player->getLevel()->getName() === "NodebuffE" or $player->getLevel()->getName() === "GappleE" or $player->getLevel()->getName() === "SumoE")
                {
                    if(EventManager::$isWaiting == true)
                    {
                        $type = "2";
                        $waiting = EventManager::$waitingTime;

                        if(EventManager::$playerCount < 2)
                        {
                            $type = "1";
                        }

                        $stamp = TimeManager::secondsToTime($waiting);


                        Main::getInstance()->getServer()->getAsyncPool()->submitTask(new AsyncEventWaitingScoreboard($player->getName(), InformationAPI::getServerRegion(), $type, $stamp, $this->round));

                    }else{
                        Main::getInstance()->getServer()->getAsyncPool()->submitTask(new AsyncEventScoreboard($player->getName(), EventManager::$playerCount, EventManager::$eliminatedCount, EventManager::$roundCount, InformationAPI::getServerRegion(), $this->round));
                    }
                } else {
                    $this->startFFA($player);
                }
            } else {
                Main::getInstance()->getServer()->getAsyncPool()->submitTask(new AsyncRemoveScoreboard($player->getName()));
            }

        }

        switch($this->round)
        {
            case "0":
                $this->round = "1";
                break;
            case "1":
                $this->round = "2";
                break;
            case "2":
                $this->round = "0";
                break;
        }
    }

    private function startFFA($player)
    {
        if($player->getLevel()->getName() !== "kitroom")
        {
            $combat = 0;
            if (isset(PlayerManager::$combat_time[$player->getName()])) {
                $combat = PlayerManager::$combat_time[$player->getName()];
            } else {
                $combat = 0;
            }

            $theirping = 0;

            if(isset(PlayerManager::$fighter[$player->getName()]))
            {
                $fplayer = Server::getInstance()->getPlayer(PlayerManager::$fighter[$player->getName()]);

                if(!is_null($fplayer))
                {
                    $theirping = $fplayer->getPing();
                }else{
                    $theirping = 0;
                }
            }

            $koth = false;
            $capturingPlayer = "null";
            $capturingTime = "";

            if($player->getLevel()->getName() == "koth")
            {
                $koth = true;
                $capturingPlayer = KOTHManager::$capturingPlayer;
                $capturingTime = KOTHManager::$captureTime;
            }

            Main::getInstance()->getServer()->getAsyncPool()->submitTask(new AsyncFFAScoreboard($player->getName(), $player->getPing(), $combat, InformationAPI::getServerRegion(), $theirping, $koth, $capturingPlayer, $capturingTime, $this->round));
        }else{
            $kit = "";

            if(isset(KitsAPI::$isEditing[$player->getName()]))
            {
                $kit = KitsAPI::$isEditing[$player->getName()];
            }

            Main::getInstance()->getServer()->getAsyncPool()->submitTask(new AsyncEditKitScoreboard($player->getName(), $kit, InformationAPI::getServerRegion(), $this->round));
        }
    }

    private function startDuel(\pocketmine\Player $player, Duels $duel)
    {
        $duration = "";

        if(!is_null($duel))
        {
            $duration = TimeManager::secondsToTime($duel->duel_duration);
        }

        $ping = "";
        $opo = $duel->getOpponent($player->getName());

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
            }
        }

        $opo = $duel->getOpponent($player->getName());
        $oname = "";

        if(!is_null($opo))
        {
            $p = Server::getInstance()->getPlayer($opo);
            if(!is_null($p))
            {
                $oname = $p->getDisplayName();
            }
        }

        Main::getInstance()->getServer()->getAsyncPool()->submitTask(new AsyncDuelScoreboard($player->getName(), $player->getPing(), $ping, $duel->getDuelTime(), InformationAPI::getServerRegion(), $duel->getStatus(), $duel->getPlayerType($player->getName()), $duration, $this->round, $oname));
    }
}