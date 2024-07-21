<?php


namespace practice\tasks\async;


use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use practice\manager\LeaderboardManager;
use practice\manager\SQLManager;

class AsyncGetLeaderboard extends AsyncTask
{
    public function onRun()
    {
        $db = SQLManager::getSQLSesionAsync();
        $elo = $db->query("SELECT `name`, `elo` FROM players ORDER BY `elo` DESC LIMIT 50")->fetch_all(MYSQLI_ASSOC);
        $wins = $db->query("SELECT `name`, `wins` FROM players ORDER BY `wins` DESC LIMIT 50")->fetch_all(MYSQLI_ASSOC);
        $loses = $db->query("SELECT `name`, `loses` FROM players ORDER BY `loses` DESC LIMIT 50")->fetch_all(MYSQLI_ASSOC);
        $kills = $db->query("SELECT `name`, `kill` FROM players ORDER BY `kill` DESC LIMIT 50")->fetch_all(MYSQLI_ASSOC);
        $deaths = $db->query("SELECT `name`, `death` FROM players ORDER BY `death` DESC LIMIT 50")->fetch_all(MYSQLI_ASSOC);
        $kill_streaks = $db->query("SELECT `name`, `kill_streak` FROM players ORDER BY `kill_streak` DESC LIMIT 50")->fetch_all(MYSQLI_ASSOC);
        $this->setResult(["elo" => $elo, "wins" => $wins, "loses" => $loses, "kills" => $kills, "death" => $deaths, "kill_streaks" => $kill_streaks]);
        $db->close();
    }

    public function onCompletion(Server $server)
    {
        Server::getInstance()->getLogger()->info("[Practice] The leaderboard update has been successfully completed.");
        LeaderboardManager::setCacheLeaderboard($this->getResult());
    }
}