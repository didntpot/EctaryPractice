<?php


namespace practice\manager;


use pocketmine\Server;
use practice\provider\WorkerProvider;
use practice\tasks\async\AsyncGetLeaderboard;

class LeaderboardManager
{

    private static $leaderboard;

    public static function getCacheLeaderboard()
    {
        return self::$leaderboard;
    }

    public static function setCacheLeaderboard($leaderboard): void
    {
        self::$leaderboard = $leaderboard;
    }

    public static function initLeaderboard()
    {
        Server::getInstance()->getLogger()->info("[Practice] Leaderboard update in progress.");
        SQLManager::sendToWorker(new AsyncGetLeaderboard(), WorkerProvider::SYSTEME_ASYNC);
    }
}