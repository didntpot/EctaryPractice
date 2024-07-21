<?php

namespace practice\duels\Task;

use pocketmine\scheduler\Task;
use pocketmine\Server;
use practice\duels\Duels;
use practice\duels\DuelsProvider;
use practice\duels\manager\DuelsManager;
use practice\Main;
use practice\manager\TimeManager;
use practice\manager\PlayerManager;
use practice\duels\events\BlockPlace;
use practice\events\listener\PlayerJoin;
use practice\scoreboard\{DuelGameScoreboard};

class Wait extends Task
{

    private $duel_id;

    public function __construct($duel_id)
    {
        $this->duel_id = $duel_id;
    }

    public function onRun(int $currentTick)
    {
        if (isset(DuelsProvider::$duels[$this->duel_id]))
        {
            $duel = DuelsProvider::$duels[$this->duel_id];
            if ($duel instanceof Duels){
                if (count($duel->getPlayers()) > 1)
                {
                    if ($duel->wait_time != 0)
                    {
                        $duel->sendSound("note.bassattack");
                        $duel->sendTitle($duel->getPlayers(), "§b".$duel->wait_time);
                        $duel->sendMessage("§eThe match starts in §b". $duel->wait_time."§e seconds...", "message");
                        $duel->wait_time--;
                    }else{
                        $duel->sendTitle($duel->getPlayers(), "§r");
                        $duel->sendMessage("§aThe match has started, good luck!", "message");
                        $this->stopTask();
                        $duel->addKit();
                        $duel->startDuel();
                        $duel->sendSound("firework.blast");

                        foreach($duel->getPlayers() as $p)
                        {
                            unset(PlayerManager::$opoInventory[$p]);
                            PlayerManager::$duelHits[$p] = 0;
                        }

                        if($duel->getKit() == "boxing")
                        {
                            $duel->sendMessage("§7- First player to 100 hits wins.", "message");
                        }

                        if($duel->getKit() == "mlgrush")
                        {
                            foreach($duel->getPlayers() as $p)
                            {
                                BlockPlace::$blocks[$p] = [];
                            }
                        }

                        if($duel->getKit() == "hikabrain")
                        {
                            foreach($duel->getPlayers() as $p)
                            {
                                BlockPlace::$blocks[$p] = [];
                            }
                        }
                    }
                }else{
                    $opo = (empty($duel->getDeadplayers())) ? null : $duel->getDeadplayers()[array_key_first($duel->getDeadplayers())];
                    $p = (empty($duel->getPlayers())) ? null : $duel->getPlayers()[array_key_first($duel->getPlayers())];

                    if (!empty($duel->getPlayers())) $duel->setWinner($p, $opo);
                    $this->stopTask();
                }
            }
        }
    }

    public function stopTask()
    {
        Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
    }
}