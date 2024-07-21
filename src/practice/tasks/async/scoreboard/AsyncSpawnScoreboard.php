<?php

namespace practice\tasks\async\scoreboard;

use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use practice\duels\DuelQueue;

class AsyncSpawnScoreboard extends AsyncTask
{
    private $region;

    public function __construct($name, $count, $region, $queue, $wQueue, $elo, $division, $round, $next)
    {
        $this->name = $name;
        $this->count = $count;
        $this->region = $region;
        $this->queue = $queue;
        $this->wQueue = $wQueue;
        $this->elo = $elo;
        $this->division = $division;
        $this->round = $round;
        $this->next = $next;
    }

    public function onRun()
    {
        $remove = new RemoveObjectivePacket();
        $remove->objectiveName = "test";

        $title = new SetDisplayObjectivePacket();
        $title->displaySlot = "sidebar";
        $title->objectiveName = "test";
        $title->displayName = " §b§l{$this->region} PRACTICE ";
        $title->criteriaName = "dummy";
        $title->sortOrder = 0;

        $entrie = new ScorePacketEntry();
        $entrie->objectiveName = "test";
        $entrie->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
        $entrie->customName = "§7-------------------- ";
        $entrie->score = 0;
        $entrie->scoreboardId = 0;
        $line1 = new SetScorePacket();
        $line1->type = 0;
        $line1->entries[] = $entrie;

        $entrie = new ScorePacketEntry();
        $entrie->objectiveName = "test";
        $entrie->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
        $entrie->customName = " §bPlaying: §f{$this->count}";
        $entrie->score = 2;
        $entrie->scoreboardId = 2;
        $line3 = new SetScorePacket();
        $line3->type = 0;
        $line3->entries[] = $entrie;

        $entrie = new ScorePacketEntry();
        $entrie->objectiveName = "test";
        $entrie->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
        $entrie->customName = " §bQueued: §f{$this->queue}";
        $entrie->score = 3;
        $entrie->scoreboardId = 3;
        $line4 = new SetScorePacket();
        $line4->type = 0;
        $line4->entries[] = $entrie;

        $entrie = new ScorePacketEntry();
        $entrie->objectiveName = "test";
        $entrie->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
        $entrie->customName = "§r";
        $entrie->score = 4;
        $entrie->scoreboardId = 4;
        $line5 = new SetScorePacket();
        $line5->type = 0;
        $line5->entries[] = $entrie;

        $entrie = new ScorePacketEntry();
        $entrie->objectiveName = "test";
        $entrie->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
        $entrie->customName = " §bElo: §f{$this->elo}";
        $entrie->score = 8;
        $entrie->scoreboardId = 8;
        $line9 = new SetScorePacket();
        $line9->type = 0;
        $line9->entries[] = $entrie;

        $entrie = new ScorePacketEntry();
        $entrie->objectiveName = "test";
        $entrie->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
        $entrie->customName = " §bDivision: §f{$this->division}";
        $entrie->score = 9;
        $entrie->scoreboardId = 9;
        $line10 = new SetScorePacket();
        $line10->type = 0;
        $line10->entries[] = $entrie;

        $entrie = new ScorePacketEntry();
        $entrie->objectiveName = "test";
        $entrie->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
        $entrie->customName = "§f§r";
        $entrie->score = 11;
        $entrie->scoreboardId = 11;
        $line12 = new SetScorePacket();
        $line12->type = 0;
        $line12->entries[] = $entrie;

        $line13 = "";
        $line14 = "";

        if($this->wQueue !== "")
        {
            $entrie = new ScorePacketEntry();
            $entrie->objectiveName = "test";
            $entrie->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
            $entrie->customName = " §bQueue: §f{$this->wQueue}";
            $entrie->score = 12;
            $entrie->scoreboardId = 12;
            $line13 = new SetScorePacket();
            $line13->type = 0;
            $line13->entries[] = $entrie;

            $entrie = new ScorePacketEntry();
            $entrie->objectiveName = "test";
            $entrie->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
            $entrie->customName = " §r§f§f";
            $entrie->score = 13;
            $entrie->scoreboardId = 13;
            $line14 = new SetScorePacket();
            $line14->type = 0;
            $line14->entries[] = $entrie;
        }

        $entrie = new ScorePacketEntry();
        $entrie->objectiveName = "test";
        $entrie->type = ScorePacketEntry::TYPE_FAKE_PLAYER;

        $name = " §7www.ectary.club";

        switch($this->round)
        {
            case 0:
                $name = " §7www.ectary.club";
                break;
            case 1:
                $name = " §9discord.gg/ectary";
                break;
            case 2:
                $name = " §bstore.ectary.club";
                break;
        }

        $entrie->customName = $name;

        $entrie->score = 14;
        $entrie->scoreboardId = 14;
        $line15 = new SetScorePacket();
        $line15->type = 0;
        $line15->entries[] = $entrie;

        $entrie = new ScorePacketEntry();
        $entrie->objectiveName = "test";
        $entrie->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
        $entrie->customName = "§7--------------------§f ";
        $entrie->score = 15;
        $entrie->scoreboardId = 15;
        $line16 = new SetScorePacket();
        $line16->type = 0;
        $line16->entries[] = $entrie;

        $packets = [];

        if($line13 !== "")
        {
            $packets = [$remove, $title, $line1, $line3, $line4, $line5, $line9, $line10, $line12, $line13, $line14, $line15, $line16];
        }else{
            $packets = [$remove, $title, $line1, $line3, $line4, $line5, $line9, $line10, $line12, $line15, $line16];
        }
        $this->setResult($packets);
    }

    public function onCompletion(Server $server)
    {
        $player = $server::getInstance()->getPlayer($this->name);
        if (is_null($player)) return;
        foreach ($this->getResult() as $result) {
            if($result instanceof DataPacket)
            {
                $player->sendDataPacket($result);
            }
        }
    }
}