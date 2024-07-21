<?php

namespace practice\game\event\tasks;

use pocketmine\scheduler\Task;
use practice\Main;
use practice\game\event\Manager;
use practice\game\event\tasks\GameTask;
use pocketmine\Server;

class RematchScheduler extends Task
{
    public $timer = 5;

    public function onRun(int $currentTick)
    {
        if (Manager::$is_running === true) {
            if ($this->timer === 0) {
                GameTask::$inmatch = false;
                Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
            } else {
                #Server::getInstance()->broadcastPopup("§a» The next match is starting in {$this->timer}s...");
                $this->timer--;
            }
        } else {
            Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
        }
    }
}