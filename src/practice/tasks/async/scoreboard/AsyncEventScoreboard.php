<?php

namespace practice\tasks\async\scoreboard;

use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class AsyncEventScoreboard extends AsyncTask
{
    public function __construct($name, $playersLeft, $eliminatedCount, $roundCount, $region, $round)
    {
        $this->name = $name;
        $this->playerLeft = $playersLeft;
        $this->eliminatedCount = $eliminatedCount;
        $this->roundCount = $roundCount;
        $this->region = $region;
        $this->round = $round;
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
        $entrie->customName = " §bPlayers Left: §f{$this->playerLeft}";
        $entrie->score = 1;
        $entrie->scoreboardId = 1;
        $line2 = new SetScorePacket();
        $line2->type = 0;
        $line2->entries[] = $entrie;

        $entrie = new ScorePacketEntry();
        $entrie->objectiveName = "test";
        $entrie->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
        $entrie->customName = " §bEliminated: §f{$this->eliminatedCount}";
        $entrie->score = 2;
        $entrie->scoreboardId = 2;
        $line3 = new SetScorePacket();
        $line3->type = 0;
        $line3->entries[] = $entrie;

        $entrie = new ScorePacketEntry();
        $entrie->objectiveName = "test";
        $entrie->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
        $entrie->customName = " §bRound: §f{$this->roundCount}";
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

        $entrie->score = 5;
        $entrie->scoreboardId = 5;
        $line6 = new SetScorePacket();
        $line6->type = 0;
        $line6->entries[] = $entrie;

        $entrie = new ScorePacketEntry();
        $entrie->objectiveName = "test";
        $entrie->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
        $entrie->customName = "§r§7-------------------- ";
        $entrie->score = 6;
        $entrie->scoreboardId = 6;
        $line7 = new SetScorePacket();
        $line7->type = 0;
        $line7->entries[] = $entrie;

        $packets = [$remove, $title, $line1, $line2, $line3, $line4, $line5, $line6, $line7];
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