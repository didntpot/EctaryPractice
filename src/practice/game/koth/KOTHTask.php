<?php

namespace practice\game\koth;

use practice\game\koth\KOTHManager as KM;
use practice\api\KitsAPI;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class KOTHTask extends Task
{
    public function onRun(int $currentTick)
    {
        foreach(Server::getInstance()->getOnlinePlayers() as $player)
        {
            $block = $player->getLevel()->getBlock($player->floor()->subtract(0, 1));
            $block1 = $player->getLevel()->getBlock($player->floor()->subtract(0, 2));

            if($player->getLevel()->getName() == "koth")
            {
                if($block->getId() == 82 or $block1->getId() == 82)
                {
                    if(KM::$capturingPlayer == null)
                    {
                        KM::$capturingPlayer = $player->getName();
                        KM::resetTimers();
                        KM::sendMessage("§a» {$player->getName()} is now capturing the KOTH.");
                    }else{
                        $KMp = KM::$capturingPlayer;

                        $p = Server::getInstance()->getPlayer($KMp);

                        if(!is_null($p))
                        {
                            if(KM::$captureTime == 70)
                            {
                                KM::sendMessage("§a» {$p->getName()} won the KOTH.");

                                if(!is_null(Server::getInstance()->getLevelByName("koth")))
                                {
                                    foreach(Server::getInstance()->getLevelByName("koth")->getPlayers() as $lp)
                                    {
                                        $lp->teleport(Server::getInstance()->getLevelByName("spawn")->getSafeSpawn());
                                        KitsAPI::addLobbyKit($lp);
                                    }
                                }

                                KM::resetAll();
                            }else{
                                if(KM::$capturingPlayer == $player->getName())
                                {
                                    KM::$captureTime = KM::$captureTime+1;
                                    $player->sendActionBarMessage("§aCapture: ".KM::$captureTime."§7/§a70");
                                }
                            }
                        }
                    }
                }else{
                    if(KM::$capturingPlayer == $player->getName())
                    {
                        KM::resetAll();
                    }
                }
            }

            $KMp = KM::$capturingPlayer;

            if(!is_null($KMp))
            {
                $player = Server::getInstance()->getPlayer($KMp);

                if(is_null($player))
                {
                    KM::sendMessage("§a» ".KM::$capturingPlayer." is no longer capturing the KOTH.");
                    KM::$capturingPlayer = null;
                    KM::resetTimers();
                }
            }
        }
    }
}