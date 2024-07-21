<?php


namespace practice\duels\Task;


use pocketmine\scheduler\Task;
use pocketmine\Server;
use practice\duels\Duels;
use practice\duels\DuelsProvider;
use practice\Main;
use practice\manager\TimeManager;

class Duel extends Task
{

    private $duel_id;

    public function __construct($duel_id)
    {
        $this->duel_id = $duel_id;
    }

    public function onRun(int $currentTick)
    {
        if (isset(DuelsProvider::$duels[$this->duel_id])) {
            $duel = DuelsProvider::$duels[$this->duel_id];
            if ($duel instanceof Duels) {
                if ($duel->duel_time >= 0)
                {
                    if (count($duel->getPlayers()) == 1)
                    {
                        $opo = (empty($duel->getDeadplayers())) ? null : $duel->getDeadplayers()[array_key_first($duel->getDeadplayers())];
                        $p = (empty($duel->getPlayers())) ? null : $duel->getPlayers()[array_key_first($duel->getPlayers())];
                        $duel->setWinner($p, $opo);
                        $this->stopTask();
                    }elseif(count($duel->getPlayers()) == 0){
                        $duel->stop();
                        $this->stopTask();
                    }else{
                        $duel->duel_time--;
                        $duel->duel_duration++;
                    }
                }else{
                    if (count($duel->getDeadplayers()) == 0) $duel->sendTitle(array_merge($duel->getPlayers(), $duel->getSpectatorPlayers(), $duel->getDeadplayers()), "§7NO WINNER");
                    $duel->startFinish();
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