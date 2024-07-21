<?php


namespace practice\tasks\async\scoreboard;


use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use practice\manager\TimeManager;

class AsyncDuelScoreboard extends AsyncTask
{

    private $remaining;
    private $name;
    private $ping_player;
    private $region;
    private $ping;
    private $status;
    private $type;
    private $duel_duration;

    public function __construct($name, $ping_me, $ping_player, $remaining, $region, $status, $type, $duel_duration, $round, $opo)
    {
        $this->name = $name;
        $this->ping = $ping_me;
        $this->ping_player = $ping_player;
        $this->region = $region;
        $this->remaining = $remaining;
        $this->status = $status;
        $this->type = $type;
        $this->duel_duration = $duel_duration;
        $this->round = $round;
        $this->opo = $opo;
    }

    public function onRun()
    {
        if ($this->type === "spectator")
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
            $entrie->customName = " §fCurrently spectating.";
            $entrie->score = 1;
            $entrie->scoreboardId = 1;
            $line2 = new SetScorePacket();
            $line2->type = 0;
            $line2->entries[] = $entrie;

            $entrie = new ScorePacketEntry();
            $entrie->objectiveName = "test";
            $entrie->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
            $entrie->customName = "§r";
            $entrie->score = 2;
            $entrie->scoreboardId = 2;
            $line3 = new SetScorePacket();
            $line3->type = 0;
            $line3->entries[] = $entrie;

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
            $entrie->score = 3;
            $entrie->scoreboardId = 3;
            $line4 = new SetScorePacket();
            $line4->type = 0;
            $line4->entries[] = $entrie;


            $entrie = new ScorePacketEntry();
            $entrie->objectiveName = "test";
            $entrie->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
            $entrie->customName = "§r§7-------------------- ";
            $entrie->score = 4;
            $entrie->scoreboardId = 4;
            $line5 = new SetScorePacket();
            $line5->type = 0;
            $line5->entries[] = $entrie;

            $packets = [$remove, $title, $line1, $line2, $line3, $line4, $line5];
            $this->setResult($packets);
            return;
        }

        switch ($this->status)
        {
            case 2:
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
                $entrie->customName = " §fMatch is starting soon.";
                $entrie->score = 1;
                $entrie->scoreboardId = 1;
                $line2 = new SetScorePacket();
                $line2->type = 0;
                $line2->entries[] = $entrie;

                $entrie = new ScorePacketEntry();
                $entrie->objectiveName = "test";
                $entrie->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
                $entrie->customName = "§r";
                $entrie->score = 2;
                $entrie->scoreboardId = 2;
                $line3 = new SetScorePacket();
                $line3->type = 0;
                $line3->entries[] = $entrie;

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
                $entrie->score = 3;
                $entrie->scoreboardId = 3;
                $line4 = new SetScorePacket();
                $line4->type = 0;
                $line4->entries[] = $entrie;


                $entrie = new ScorePacketEntry();
                $entrie->objectiveName = "test";
                $entrie->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
                $entrie->customName = "§r§7-------------------- ";
                $entrie->score = 4;
                $entrie->scoreboardId = 4;
                $line5 = new SetScorePacket();
                $line5->type = 0;
                $line5->entries[] = $entrie;

                $packets = [$remove, $title, $line1, $line2, $line3, $line4, $line5];
                $this->setResult($packets);
                break;
            case 3:
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
                $line0 = new SetScorePacket();
                $line0->type = 0;
                $line0->entries[] = $entrie;

                $entrie = new ScorePacketEntry();
                $entrie->objectiveName = "test";
                $entrie->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
                $entrie->customName = " §bFighting:";
                $entrie->score = 1;
                $entrie->scoreboardId = 1;
                $line1 = new SetScorePacket();
                $line1->type = 0;
                $line1->entries[] = $entrie;

                $entrie = new ScorePacketEntry();
                $entrie->objectiveName = "test";
                $entrie->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
                $entrie->customName = " §c{$this->opo} §7| {$this->ping_player}ms";
                $entrie->score = 2;
                $entrie->scoreboardId = 2;
                $line2 = new SetScorePacket();
                $line2->type = 0;
                $line2->entries[] = $entrie;

                //Ping au joueur
                $entrie = new ScorePacketEntry();
                $entrie->objectiveName = "test";
                $entrie->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
                $entrie->customName = "§r§f";
                $entrie->score = 3;
                $entrie->scoreboardId = 3;
                $line3 = new SetScorePacket();
                $line3->type = 0;
                $line3->entries[] = $entrie;

                //Ping de l'adversaire
                $entrie = new ScorePacketEntry();
                $entrie->objectiveName = "test";
                $entrie->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
                $entrie->customName = " §bYour Ping: §f{$this->ping}ms";
                $entrie->score = 4;
                $entrie->scoreboardId = 4;
                $line4 = new SetScorePacket();
                $line4->type = 0;
                $line4->entries[] = $entrie;

                //vide
                $entrie = new ScorePacketEntry();
                $entrie->objectiveName = "test";
                $entrie->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
                $entrie->customName = " §bDuration: §f{$this->duel_duration}";
                $entrie->score = 5;
                $entrie->scoreboardId = 5;
                $line5 = new SetScorePacket();
                $line5->type = 0;
                $line5->entries[] = $entrie;

                $entrie = new ScorePacketEntry();
                $entrie->objectiveName = "test";
                $entrie->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
                $entrie->customName = "§r§f§r ";
                $entrie->score = 6;
                $entrie->scoreboardId = 6;
                $line6 = new SetScorePacket();
                $line6->type = 0;
                $line6->entries[] = $entrie;

                //Ip
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
                $entrie->score = 7;
                $entrie->scoreboardId = 7;
                $line7 = new SetScorePacket();
                $line7->type = 0;
                $line7->entries[] = $entrie;


                $entrie = new ScorePacketEntry();
                $entrie->objectiveName = "test";
                $entrie->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
                $entrie->customName = "§r§7-------------------- ";
                $entrie->score = 8;
                $entrie->scoreboardId = 8;
                $line8 = new SetScorePacket();
                $line8->type = 0;
                $line8->entries[] = $entrie;

                $packets = [$remove, $title, $line0, $line1, $line2, $line3, $line4, $line5, $line6, $line7, $line8];
                $this->setResult($packets);
                break;
            case 4:
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
                $entrie->customName = " §fMatch has ended.";
                $entrie->score = 1;
                $entrie->scoreboardId = 1;
                $line2 = new SetScorePacket();
                $line2->type = 0;
                $line2->entries[] = $entrie;

                $entrie = new ScorePacketEntry();
                $entrie->objectiveName = "test";
                $entrie->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
                $entrie->customName = "§r";
                $entrie->score = 2;
                $entrie->scoreboardId = 2;
                $line3 = new SetScorePacket();
                $line3->type = 0;
                $line3->entries[] = $entrie;

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
                $entrie->score = 3;
                $entrie->scoreboardId = 3;
                $line4 = new SetScorePacket();
                $line4->type = 0;
                $line4->entries[] = $entrie;


                $entrie = new ScorePacketEntry();
                $entrie->objectiveName = "test";
                $entrie->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
                $entrie->customName = "§r§7-------------------- ";
                $entrie->score = 4;
                $entrie->scoreboardId = 4;
                $line5 = new SetScorePacket();
                $line5->type = 0;
                $line5->entries[] = $entrie;

                $packets = [$remove, $title, $line1, $line2, $line3, $line4, $line5];
                $this->setResult($packets);
                break;
            default:
                $this->setResult([]);
                break;
        }
    }

    public function onCompletion(Server $server)
    {
        if (!empty($this->getResult()))
        {
            $player = $server::getInstance()->getPlayer($this->name);
            if (is_null($player)) return;
            foreach ($this->getResult() as $result) {
                $player->sendDataPacket($result);
            }
        }
    }
}