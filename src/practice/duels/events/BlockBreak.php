<?php

namespace practice\duels\events;

use pocketmine\block\Block;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use practice\duels\manager\DuelsManager;
use practice\manager\PlayerManager;
use pocketmine\scheduler\Task;
use practice\api\KitsAPI;
use pocketmine\Server;
use practice\Main;
use pocketmine\level\Location;
use practice\api\SoundAPI;
use pocketmine\math\Vector3;
use practice\duels\events\BlockPlace;

class BlockBreak implements Listener
{

    public function onBreakDD(BlockBreakEvent $event)
    {
        $player = $event->getPlayer();
        $block = $event->getBlock();

        if(DuelsManager::isInDuel($player->getName()))
        {
            $duel = DuelsManager::getDuel($player->getName());
            if (!is_null($duel) and !$duel->isSpectator($player->getName())){

                if($duel->getKit() === "spleef")
                {
                    if($block->getId() !== 80) $event->setCancelled();
                }elseif($duel->getKit() === "builduhc" or $duel->getKit() === "finaluhc" or $duel->getKit() === "skywars" or $duel->getKit() === "caveuhc" or $duel->getKit() === "hg"or $duel->getKit() === "mlgrush"or $duel->getKit() === "thebridge"or $duel->getKit() === "hikabrain") {
                    if ($event->getBlock()->getId() !== Block::COBBLESTONE && $event->getBlock()->getId() !== 24 && $event->getBlock()->getId() !== 35) $event->setCancelled();
                }else{
                    $event->setCancelled();
                    $event->setDrops([]);
                }

                if($duel->getKit() == "mlgrush")
                {
                    if(isset(PlayerManager::$playerPoints[$player->getName()]) && isset(PlayerManager::$playerTeam[$player->getName()]))
                    {
                        switch(PlayerManager::$playerTeam[$player->getName()])
                        {
                            case "red":
                                if($block->getId() == 26 && $block->getDamage() == 2 or $block->getDamage() == 10)
                                {
                                    PlayerManager::$playerPoints[$player->getName()]++;

                                    $opo = $duel->getOpponent($player->getName());

                                    if(!is_null($opo))
                                    {
                                        $opp = Server::getInstance()->getPlayer($opo);

                                        if(!is_null($opp))
                                        {
                                            $opo = $opp;
                                            $player->getInventory()->clearAll();
                                            $opo->getInventory()->clearAll();

                                            if(PlayerManager::$playerPoints[$player->getName()] !== 3 && PlayerManager::$playerPoints[$opo->getName()] < 3)
                                            {
                                                $player->setImmobile();
                                                $opo->setImmobile();
                                                $player->teleport(new Location(256.5000, 21, 284.5000, 0, 0, $player->getLevel()));
                                                $opo->teleport(new Location(256.5000, 21, 254.5000, 0, 0, $player->getLevel()));
                                                $player->sendMessage("§eMLG-Rush §l» §r§a{$player->getDisplayName()} scored.");
                                                $opo->sendMessage("§eMLG-Rush §l» §r§a{$player->getDisplayName()} scored.");

                                                if(isset(BlockPlace::$blocks[$player->getName()]))
                                                {
                                                    $blockss = BlockPlace::$blocks[$player->getName()];
                                                    foreach ($blockss as $block)
                                                    {
                                                        $b = explode(':', $block);
                                                        $player->getLevel()->setBlock(new Vector3($b['0'], $b['1'], $b['2']), Block::get(0));
                                                    }
                                                }

                                                if(isset(BlockPlace::$blocks[$opo->getName()]))
                                                {
                                                    $blockss = BlockPlace::$blocks[$opo->getName()];
                                                    foreach($blockss as $block)
                                                    {
                                                        $b = explode(':', $block);
                                                        $opo->getLevel()->setBlock(new Vector3($b['0'], $b['1'], $b['2']), Block::get(0));
                                                    }
                                                }

                                                Main::getInstance()->getScheduler()->scheduleRepeatingTask(new MLGRushScheduler($player->getName(), $opo->getName()), 20);
                                            }
                                        }
                                    }
                                }
                                break;
                            case "blue":
                                if($block->getId() == 26 && $block->getDamage() == 0 or $block->getDamage() == 8)
                                {
                                    PlayerManager::$playerPoints[$player->getName()]++;

                                    $opo = $duel->getOpponent($player->getName());

                                    if(!is_null($opo))
                                    {
                                        $opp = Server::getInstance()->getPlayer($opo);

                                        if(!is_null($opp))
                                        {
                                            $opo = $opp;
                                            $player->getInventory()->clearAll();
                                            $opo->getInventory()->clearAll();

                                            if(PlayerManager::$playerPoints[$player->getName()] !== 3 && PlayerManager::$playerPoints[$opo->getName()] < 3)
                                            {
                                                $player->setImmobile();
                                                $opo->setImmobile();
                                                $opo->teleport(new Location(256.5000, 21, 284.5000, 0, 0, $player->getLevel()));
                                                $player->teleport(new Location(256.5000, 21, 254.5000, 0, 0, $player->getLevel()));
                                                $player->sendMessage("§eMLG-Rush §l» §r§a{$player->getDisplayName()} scored.");
                                                $opo->sendMessage("§eMLG-Rush §l» §r§a{$player->getDisplayName()} scored.");

                                                if(isset(BlockPlace::$blocks[$player->getName()]))
                                                {
                                                    $blockss = BlockPlace::$blocks[$player->getName()];
                                                    foreach ($blockss as $block)
                                                    {
                                                        $b = explode(':', $block);
                                                        $player->getLevel()->setBlock(new Vector3($b['0'], $b['1'], $b['2']), Block::get(0));
                                                    }
                                                }

                                                if(isset(BlockPlace::$blocks[$opo->getName()]))
                                                {
                                                    $blockss = BlockPlace::$blocks[$opo->getName()];
                                                    foreach($blockss as $block)
                                                    {
                                                        $b = explode(':', $block);
                                                        $opo->getLevel()->setBlock(new Vector3($b['0'], $b['1'], $b['2']), Block::get(0));
                                                    }
                                                }

                                                Main::getInstance()->getScheduler()->scheduleRepeatingTask(new MLGRushScheduler($opo->getName(), $player->getName()), 20);
                                            }
                                        }
                                    }
                                }
                                break;
                        }

                        if(PlayerManager::$playerPoints[$player->getName()] == 3)
                        {
                            $opo = $duel->getOpponent($player->getName());

                            if(!is_null($opo))
                            {
                                $oppo = Server::getInstance()->getPlayer($opo);

                                if(!is_null($oppo))
                                {
                                    $duel->addDeath($oppo);
                                }
                            }

                        }
                    }
                }
            }
            $event->setDrops([]);
        }
    }
}

class MLGRushScheduler extends Task
{
    public $timer = 4;

    public function __construct($playerRed, $playerBlue)
    {
        $this->playerRed = $playerRed;
        $this->playerBlue = $playerBlue;
    }

    public function onRun(int $currentTick)
    {
        $playerRed = Server::getInstance()->getPlayer($this->playerRed);
        $playerBlue = Server::getInstance()->getPlayer($this->playerBlue);

        if(!is_null($playerRed) && !is_null($playerBlue))
        {
            if($this->timer == 0)
            {
                $playerRed->sendTitle("§r");
                $playerBlue->sendTitle("§r");
                $playerRed->setImmobile(false);
                $playerBlue->setImmobile(false);
                KitsAPI::addMLGRushKit($playerRed);
                KitsAPI::addMLGRushKit($playerBlue);
                SoundAPI::playSound($playerRed, "firework.blast");
                SoundAPI::playSound($playerBlue, "firework.blast");
                Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
            }else{
                $this->timer--;
                if($this->timer != 0)
                {
                    $playerRed->sendTitle("§b{$this->timer}");
                    $playerBlue->sendTitle("§b{$this->timer}");
                    SoundAPI::playSound($playerRed, "note.bassattack");
                    SoundAPI::playSound($playerBlue, "note.bassattack");
                }
            }
        }else{
            Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
        }
    }
}