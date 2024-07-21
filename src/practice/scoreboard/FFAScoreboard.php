<?php

namespace practice\scoreboard;

use practice\api\InformationAPI;
use practice\api\PlayerDataAPI;
use practice\events\listener\{PlayerJoin};
use pocketmine\{Player};
use pocketmine\network\mcpe\protocol\{
    BatchPacket,
    RemoveObjectivePacket,
    SetDisplayObjectivePacket,
    SetScorePacket};
use practice\manager\PlayerManager;
use pocketmine\network\mcpe\protocol\types\{ScorePacketEntry};
use pocketmine\Server;
use practice\duels\DuelQueue;

class FFAScoreboard
{
    /** @var Player */
    private $player;

    /**
     * @var string
     */
    public $displayname = "";

    /**
     * @var string
     */
    public $datas =
        [
            //0 => "line1"
        ];

    /**
     * @var string|null
     */
    public $objectiveName = null;

    /**
     * BossBar constructor.
     * @param Player $player
     */
    public function __construct(Player $player)
    {
        $this->player = $player;
        $this->objectiveName = "" . $player->getId() . "";
        $this->displayname = " ";
    }

    /**
     * @return Player
     */
    public function getPlayer():Player
    {
        return $this->player;
    }

    /**
     * @return string
     */
    public function getDisplayName():string
    {
        return $this->displayname;
    }

    /**
     * @param string $display
     * @return $this
     */
    public function setDisplayName(string $display = ""):self
    {
        if($display !== $this->getDisplayName())
        {
            $this->displayname = $display;
        }

        return $this;
    }

    /**
     * @return string[]
     */
    public function getData():array
    {
        return $this->datas;
    }

    public function setLine(int $number, string $customname):self
    {
        if(isset($this->datas[$number]))
        {
            if($this->datas[$number] == $customname)
            {
                return $this;
            }
        }

        $this->datas[$number]  = $customname;

        return $this;
    }

    public function sendRemoveObjectivePacket():void
    {
        $pk = new RemoveObjectivePacket();
        $pk->objectiveName = $this->objectiveName;
        $this->getPlayer()->dataPacket($pk);
    }

    public function set():void
    {
        $batch = new BatchPacket();
        $batch2 = new BatchPacket();
        $batch->addPacket($this->setScorePacket(array_keys($this->getData())));

        $pk = new SetScorePacket();
        $pk->type = SetScorePacket::TYPE_CHANGE;
        $pk->entries = array_map(function (string $text, int $score)
        {

            $entry = new ScorePacketEntry();
            $entry->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
            $entry->objectiveName = $this->objectiveName;
            $entry->scoreboardId = $score;
            $entry->score = $score;
            $entry->customName = $text . " ";

            return $entry;
        }, ($texts = array_values($this->getData())), array_keys($this->getData()));

        $batch->addPacket($pk);

        $pk1 = new SetDisplayObjectivePacket();
        $pk1->displayName = $this->displayname;
        $pk1->objectiveName = $this->objectiveName;
        $pk1->displaySlot = 'sidebar';
        $pk1->criteriaName = 'dummy';
        $pk1->sortOrder = 0;

        $batch2->addPacket($pk1);

        $this->getPlayer()->sendDataPacket($batch2);
        $this->getPlayer()->sendDataPacket($batch);
    }

    public function setScorePacket(array $lines):SetScorePacket
    {
        $pk = new SetScorePacket();
        $pk->type = SetScorePacket::TYPE_REMOVE;

        foreach($lines as $score)
        {
            $entry = new ScorePacketEntry;
            $entry->objectiveName = $this->objectiveName;
            $entry->score = $score;
            $entry->scoreboardId = $score;
            $pk->entries[] = $entry;
        }

        return $pk;
    }

    public static function createLines($player)
    {
        if($player instanceof Player)
        {
            $player->scoreboard = "ffa";

            $name = $player->getName();

            $combat = 0;
            $enemyPing = 0;

            if(isset(PlayerManager::$combat_time[$name]))
            {
                $combat = PlayerManager::$combat_time[$name];
            }else{
                $combat = 0;
            }


            if(isset(PlayerManager::$fighter[$player->getName()]))
            {
                $fplayer = Server::getInstance()->getPlayer(PlayerManager::$fighter[$player->getName()]);

                if(!is_null($fplayer))
                {
                    $enemyPing = $fplayer->getPing();
                }else{
                    $enemyPing = 0;
                }
            }

            PlayerJoin::$scoreboard[$name]
                ->setDisplayName("  §b§l".InformationAPI::getServerRegion()." Practice  ")
                ->setLine(1, "§7-------------------- ")
                ->setLine(2, " §aYour Ping: §f{$player->getPing()}ms")
                ->setLine(3, " §cTheir Ping: §f{$enemyPing}ms")
                ->setLine(4, " §4Combat: §f{$combat}s")
                ->setLine(8, "§r")
                ->setLine(9, " {$player->scoreboardRound}")
                ->setLine(10, "§r§7-------------------- ")
                ->set();{
        }
        }
    }
}