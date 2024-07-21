<?php


namespace practice\tasks\server;


use pocketmine\scheduler\Task;
use pocketmine\Server;

class DayTask extends Task
{

    public function onRun(int $currentTick)
    {
        foreach (Server::getInstance()->getLevels() as $level) {
            $level->setTime(1000);
        }
    }
}