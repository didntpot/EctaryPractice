<?php


namespace practice\duels\Task;


use pocketmine\scheduler\Task;
use practice\duels\Duels;
use practice\duels\DuelsProvider;
use practice\Main;

class Finish extends Task
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
            if ($duel instanceof Duels) {
                if (empty($duel->getPlayers()))
                {
                    $duel->stop();
                    $this->stopTask();
                }else{
                    if ($duel->getWaitFinishTime() >= 0)
                    {
                        $duel->wait_finish_time--;
                    }else{
                        $this->stopTask();
                        $duel->stop();
                    }
                }
            }else{
                $this->stopTask();
            }
        }
    }

    public function stopTask()
    {
        Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
    }
}