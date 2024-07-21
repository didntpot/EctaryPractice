<?php

namespace practice\commands\vote;

use practice\api\PlayerDataAPI;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;
use pocketmine\utils\TextFormat;
use practice\manager\PlayerManager;
use practice\manager\TempRankManager;

class ProcessVoteTask extends AsyncTask
{

    /** @var string $apiKey */
    private $apiKey;
    /** @var string $username */
    private $username;

    public function __construct(string $apiKey, string $username)
    {
        $this->apiKey = $apiKey;
        $this->username = $username;
    }

    public function onRun(): void
    {
        $result = Internet::getURL("https://minecraftpocket-servers.com/api/?object=votes&element=claim&key=" . $this->apiKey . "&username=" . str_replace(" ", "+", $this->username));
        if ($result === "1") Internet::getURL("https://minecraftpocket-servers.com/api/?action=post&object=votes&element=claim&key=" . $this->apiKey . "&username=" . str_replace(" ", "+", $this->username));
        $this->setResult($result);
    }

    public function onCompletion(Server $server): void
    {
        $result = $this->getResult();
        $player = $server->getPlayer($this->username);
        if ($player === null) return;
        switch ($result) {
            case "0":
                $player->sendMessage("§c» You didn't voted yet, vote for free at http://ectary.club/vote and run /vote command.");
                return;
            case "1":
                $coins = mt_rand(150, 200);
                if (PlayerManager::getInformation($player->getName(), "group") === "Basic")
                {
                    TempRankManager::setTempGroup($player->getName(), "Voter", strtotime("12hour"));
                    $player->sendMessage("§a» Thank for voting, you've received $coins coins and a 24 hours Voter rank!");
                    Server::getInstance()->broadcastMessage("§6§l§k|§e|§6|§r §a{$player->getName()} voted for free at https://ectary.club/vote and won $coins coins and also a Voter rank! §6§l§k|§e|§6|§r");
                }else{
                    $player->sendMessage("§a» Thank for voting, you've received $coins coins!");
                    Server::getInstance()->broadcastMessage("§6§l§k|§e|§6|§r §a{$player->getName()} voted for free at https://ectary.club/vote and won $coins coins! §6§l§k|§e|§6|§r");
                }

                PlayerManager::setInformation($player->getName(), "coins", PlayerManager::getInformation($player->getName(), "coins") + $coins);
                return;
            case "2":
                $player->sendMessage("§c» You've arleady voted today.");
                return;
            default:
                $player->sendMessage("§cError (Vote Error 1)");
                return;
        }
    }
}
