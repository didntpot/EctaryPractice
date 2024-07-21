<?php

namespace practice\tasks\async\scoreboard;

use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class AsyncRemoveScoreboard extends AsyncTask
{
    public function __construct($name)
    {
        $this->name = $name;
    }

    public function onRun()
    {
        $remove = new RemoveObjectivePacket();
        $remove->objectiveName = "test";

        $packets = [$remove];
        $this->setResult($packets);
    }

    public function onCompletion(Server $server)
    {
        $player = $server::getInstance()->getPlayer($this->name);
        if (is_null($player)) return;
        foreach ($this->getResult() as $result) {
            $player->sendDataPacket($result);
        }
    }
}