<?php

namespace practice\tasks;

use practice\manager\PlayerManager;
use pocketmine\Server;
use pocketmine\scheduler\Task;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;

class Vanish extends Task
{
    public function onRun(int $currentTick)
    {
        foreach (Server::getInstance()->getOnlinePlayers() as $players) {
            if($players->getLevel()->getName() !== "kitroom")
            {
                if (isset(PlayerManager::$staff_mode[$players->getName()]) and PlayerManager::$staff_mode[$players->getName()] === true)
                {
                    $players->sendTip("§aYOU'RE CURRENTLY VANISHED");
                    foreach (Server::getInstance()->getOnlinePlayers() as $p) {
                        if ($p->hasPermission("staff.command")) {
                            $p->showPlayer($players);
                        } else {
                            $p->hidePlayer($players);
                        }
                    }
                } else {
                    foreach (Server::getInstance()->getOnlinePlayers() as $p) {
                        $p->showPlayer($players);
                        $p->setNameTag("§c{$p->getDisplayName()}");
                    }
                }
            }
        }
    }
}