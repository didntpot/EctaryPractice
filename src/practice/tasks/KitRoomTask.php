<?php

namespace practice\tasks;

use pocketmine\Server;
use pocketmine\scheduler\Task;

class KitRoomTask extends Task
{
    public function onRun(int $currentTick)
    {
        foreach(Server::getInstance()->getOnlinePlayers() as $player)
        {
            if($player->getLevel()->getName() == "kitroom")
            {
                foreach(Server::getInstance()->getOnlinePlayers() as $p)
                {
                    $p->hidePlayer($player);
                }
            }
        }
    }
}