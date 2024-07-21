<?php


namespace practice\tasks;


use pocketmine\scheduler\Task;
use pocketmine\Server;
use practice\manager\LeaderboardManager;

class ReloadLeaderboard extends Task
{

    public function onRun(int $currentTick)
    {
        LeaderboardManager::initLeaderboard();
    }
}