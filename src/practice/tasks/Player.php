<?php

namespace practice\tasks;

use pocketmine\scheduler\Task;
use pocketmine\block\Water;
use pocketmine\Server;
use pocketmine\event\entity\EntityDamageEvent;
use practice\api\SoundAPI;
use practice\duels\events\BlockPlace;
use practice\duels\manager\DuelsManager;
use practice\game\event\Manager;
use practice\Main;
use practice\manager\LevelManager;
use practice\api\InformationAPI;
use practice\manager\PlayerManager;
use pocketmine\level\Location;
use practice\api\KitsAPI;
use pocketmine\level\Position;
use pocketmine\block\Block;
use pocketmine\math\Vector3;

class Player extends Task
{
    //Ajout de config pour plusieur monde
    const WORLD_NAME = ["spawn"];

    public function onRun(int $currentTick)
    {
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            {
                switch($player->scoreboardRound)
                {
                    case "§7www.ectary.club":
                        $player->scoreboardRound = "§9discord.gg/ectary";
                        break;
                    case "§9discord.gg/ectary":
                        $player->scoreboardRound = "§bstore.ectary.club";
                        break;
                    case "§bstore.ectary.club":
                        $player->scoreboardRound = "§7www.ectary.club";
                        break;
                }

                if (in_array($player->getLevel()->getName(), self::WORLD_NAME))
                {
                    if ($player->getY() <= 0) {
                        LevelManager::teleportSpawn($player);
                    }
                }

                $player->setFood(20);
                $player->setSaturation(20);

                if($player->getLevel()->getBlock($player) instanceof Water)
                {
                    if ((DuelsManager::isInDuel($player->getName()) and !is_null(DuelsManager::getDuel($player->getName())) and DuelsManager::getDuel($player->getName())->getKit() === "sumo")  or (isset(Manager::$is_ingame[$player->getName()]) and !is_null(Manager::$event_mode) and Manager::$event_mode === "sumo"))
                    {
                        $player->removeAllEffects();
                        $player->attack(new EntityDamageEvent($player, EntityDamageEvent::CAUSE_CUSTOM, 19));
                        $player->attack(new EntityDamageEvent($player, EntityDamageEvent::CAUSE_CUSTOM, 19));
                    }
                }

                if($player->getLevel()->getBlock($player) instanceof Water)
                {
                    if ((DuelsManager::isInDuel($player->getName()) and !is_null(DuelsManager::getDuel($player->getName())) and DuelsManager::getDuel($player->getName())->getKit() === "oneline")  or (isset(Manager::$is_ingame[$player->getName()]) and !is_null(Manager::$event_mode) and Manager::$event_mode === "sumo"))
                    {
                        $player->removeAllEffects();
                        $player->attack(new EntityDamageEvent($player, EntityDamageEvent::CAUSE_CUSTOM, 19));
                    }
                }

                if((DuelsManager::isInDuel($player->getName()) && !is_null(DuelsManager::getDuel($player->getName())) && DuelsManager::getDuel($player->getName())->getKit() === "boxing"))
                {
                    $opo = DuelsManager::getDuel($player->getName())->getOpponent($player->getName());

                    if(!is_null($opo))
                    {
                        if(isset(PlayerManager::$boxingHits[$player->getName()]) && isset(PlayerManager::$boxingHits[$opo]))
                        {
                            $player->sendActionBarMessage("§aYOU: ".PlayerManager::$boxingHits[$player->getName()]."/hits §7| §cTHEIR: ".PlayerManager::$boxingHits[$opo]."/hits");

                        }else{
                            PlayerManager::$boxingHits[$player->getName()] = 0;
                            PlayerManager::$boxingHits[$opo] = 0;
                        }
                    }
                }

                if((DuelsManager::isInDuel($player->getName()) && !is_null(DuelsManager::getDuel($player->getName())) && DuelsManager::getDuel($player->getName())->getKit() === "mlgrush"))
                {
                    if(!isset(PlayerManager::$playerTeam[$player->getName()]))
                    {
                        $block = $player->getLevel()->getBlock($player->floor()->subtract(0, 1));

                        if($block->getId() == 241 && $block->getDamage() == 14)
                        {
                            PlayerManager::$playerTeam[$player->getName()] = "red";
                            PlayerManager::$playerPoints[$player->getName()] = 0;
                        }

                        if($block->getId() == 241 && $block->getDamage() == 11)
                        {
                            PlayerManager::$playerTeam[$player->getName()] = "blue";
                            PlayerManager::$playerPoints[$player->getName()] = 0;
                        }
                    }else{
                        if(isset(PlayerManager::$playerPoints[$player->getName()]))
                        {
                            $opo = DuelsManager::getDuel($player->getName())->getOpponent($player->getName());

                            if(!is_null($opo))
                            {
                                $point_opo = (isset(PlayerManager::$playerPoints[$opo])) ? PlayerManager::$playerPoints[$opo] : 0;
                                $point = (isset(PlayerManager::$playerPoints[$player->getName()])) ? PlayerManager::$playerPoints[$player->getName()] : 0;

                                switch(PlayerManager::$playerTeam[$player->getName()])
                                {
                                    case "red":
                                        $player->sendActionBarMessage("§7(You) §cRED: ". $point ." §7| §bBLUE: ".$point_opo);
                                        break;
                                    case "blue":
                                        $player->sendActionBarMessage("§cRED: ". $point_opo ." §7| (You) §bBLUE: ". $point);
                                        break;
                                }
                            }
                        }
                    }

                    if($player->getY() < 11)
                    {
                        if(isset(PlayerManager::$playerTeam[$player->getName()]))
                        {
                            switch(PlayerManager::$playerTeam[$player->getName()])
                            {
                                case "red":
                                    $player->teleport(new Location(256.5000, 21, 284.5000, 179.5000, 0, $player->getLevel()));
                                    break;
                                case "blue":
                                    $player->teleport(new Location(256.5000, 21, 254.5000, 359.5000, 0, $player->getLevel()));
                                    break;
                            }
                        }

                        KitsAPI::addMLGRushKit($player);
                    }
                }

                if((DuelsManager::isInDuel($player->getName()) && !is_null(DuelsManager::getDuel($player->getName())) && DuelsManager::getDuel($player->getName())->getKit() === "hikabrain"))
                {
                    if(!isset(PlayerManager::$playerTeam[$player->getName()]))
                    {
                        $block = $player->getLevel()->getBlock($player->floor()->subtract(0, 1));

                        if($block->getId() == 241 && $block->getDamage() == 14)
                        {
                            PlayerManager::$playerTeam[$player->getName()] = "red";
                            PlayerManager::$playerPoints[$player->getName()] = 0;
                        }

                        if($block->getId() == 241 && $block->getDamage() == 11)
                        {
                            PlayerManager::$playerTeam[$player->getName()] = "blue";
                            PlayerManager::$playerPoints[$player->getName()] = 0;
                        }
                    }else{
                        if(isset(PlayerManager::$playerPoints[$player->getName()]))
                        {
                            $opo = DuelsManager::getDuel($player->getName())->getOpponent($player->getName());

                            if(!is_null($opo))
                            {
                                if(isset(PlayerManager::$playerPoints[$opo]))
                                {
                                    switch(PlayerManager::$playerTeam[$player->getName()])
                                    {
                                        case "red":

                                            $player->sendActionBarMessage("§7(You) §cRED: ".PlayerManager::$playerPoints[$player->getName()]." §7| §bBLUE: ".PlayerManager::$playerPoints[$opo]);

                                            $obj = new Position(254, 49, 269, $player->getLevel());

                                            if($player->distance($obj) < 1.5)
                                            {
                                                if(!isset(PlayerManager::$finished[$player->getName()]))
                                                {
                                                    PlayerManager::$playerPoints[$player->getName()] = PlayerManager::$playerPoints[$player->getName()]+1;
                                                }

                                                if(PlayerManager::$playerPoints[$player->getName()] > 2)
                                                {
                                                    $duel = DuelsManager::getDuel($player->getName());

                                                    if(!is_null($duel))
                                                    {
                                                        if(!isset(PlayerManager::$finished[$player->getName()]))
                                                        {
                                                            PlayerManager::$finished[$player->getName()] = true;
                                                            $duel->setWinner($player->getName(), $opo);
                                                        }
                                                    }
                                                }else{
                                                    $player->teleport(new Location(295.5000, 54, 269.5000, 90.5000, 87.4, $player->getLevel()));

                                                    $opoo = Server::getInstance()->getPlayer($opo);

                                                    if(!is_null($opoo))
                                                    {
                                                        $opoo->teleport(new Location(255.5000, 54, 269.5000, 269.5000, 1.5, $player->getLevel()));
                                                        $opoo->setImmobile();
                                                        $player->setImmobile();
                                                        $player->sendMessage("§eHikabrain §l» §r§a{$player->getDisplayName()} scored.");
                                                        $opoo->sendMessage("§eHikabrain §l» §r§a{$player->getDisplayName()} scored.");
                                                        $player->getArmorInventory()->clearAll();
                                                        $opoo->getArmorInventory()->clearAll();
                                                        $player->getInventory()->clearAll();
                                                        $opoo->getInventory()->clearAll();

                                                        Main::getInstance()->getScheduler()->scheduleRepeatingTask(new HikabrainScheduler($player->getName(), $opoo->getName()), 20);
                                                    }
                                                }
                                            }

                                            break;
                                        case "blue":

                                            $player->sendActionBarMessage("§cRED: ".PlayerManager::$playerPoints[$opo]." §7| (You) §bBLUE: ".PlayerManager::$playerPoints[$player->getName()]);

                                            $obj = new Position(296, 49, 269, $player->getLevel());

                                            if($player->distance($obj) < 1.5)
                                            {
                                                if(!isset(PlayerManager::$finished[$player->getName()]))
                                                {
                                                    PlayerManager::$playerPoints[$player->getName()] = PlayerManager::$playerPoints[$player->getName()]+1;
                                                }

                                                if(PlayerManager::$playerPoints[$player->getName()] > 2)
                                                {
                                                    $duel = DuelsManager::getDuel($player->getName());

                                                    if(!is_null($duel))
                                                    {
                                                        if(!isset(PlayerManager::$finished[$player->getName()]))
                                                        {
                                                            PlayerManager::$finished[$player->getName()] = true;
                                                            $duel->setWinner($player->getName(), $opo);
                                                        }
                                                    }
                                                }else{
                                                    $player->teleport(new Location(255.5000, 54, 269.5000, 269.5000, 1.5, $player->getLevel()));

                                                    $opoo = Server::getInstance()->getPlayer($opo);

                                                    if(!is_null($opoo))
                                                    {
                                                        $opoo->teleport(new Location(295.5000, 54, 269.5000, 90.5000, 87.4, $player->getLevel()));
                                                        $opoo->setImmobile();
                                                        $player->setImmobile();
                                                        $player->sendMessage("§eHikabrain §l» §r§a{$player->getDisplayName()} scored.");
                                                        $opoo->sendMessage("§eHikabrain §l» §r§a{$player->getDisplayName()} scored.");
                                                        $player->getArmorInventory()->clearAll();
                                                        $opoo->getArmorInventory()->clearAll();
                                                        $player->getInventory()->clearAll();
                                                        $opoo->getInventory()->clearAll();

                                                        Main::getInstance()->getScheduler()->scheduleRepeatingTask(new HikabrainScheduler($opoo->getName(), $player->getName()), 20);
                                                    }
                                                }
                                            }
                                            break;
                                    }
                                }
                            }
                        }
                    }

                    if($player->getY() < 41)
                    {
                        if(isset(PlayerManager::$playerTeam[$player->getName()]))
                        {
                            switch(PlayerManager::$playerTeam[$player->getName()])
                            {
                                case "red":
                                    $player->teleport(new Location(295.5000, 54, 269.5000, 90.5000, 87.4, $player->getLevel()));
                                    break;
                                case "blue":
                                    $player->teleport(new Location(255.5000, 54, 269.5000, 269.5000, 1.5, $player->getLevel()));
                                    break;
                            }
                        }

                        KitsAPI::addHikabrainKit($player);
                    }
                }

                if((DuelsManager::isInDuel($player->getName()) && !is_null(DuelsManager::getDuel($player->getName())) && DuelsManager::getDuel($player->getName())->getKit() === "thebridge"))
                {
                    if(!isset(PlayerManager::$playerTeam[$player->getName()]))
                    {
                        $block = $player->getLevel()->getBlock($player->floor()->subtract(0, 1));

                        if($block->getId() == 241 && $block->getDamage() == 14)
                        {
                            PlayerManager::$playerTeam[$player->getName()] = "red";
                            PlayerManager::$playerPoints[$player->getName()] = 0;
                        }

                        if($block->getId() == 241 && $block->getDamage() == 3)
                        {
                            PlayerManager::$playerTeam[$player->getName()] = "blue";
                            PlayerManager::$playerPoints[$player->getName()] = 0;
                        }
                    }else{
                        if(isset(PlayerManager::$playerPoints[$player->getName()]))
                        {
                            $opo = DuelsManager::getDuel($player->getName())->getOpponent($player->getName());

                            if(!is_null($opo))
                            {
                                if(isset(PlayerManager::$playerPoints[$opo]))
                                {
                                    switch(PlayerManager::$playerTeam[$player->getName()])
                                    {
                                        case "red":

                                            $player->sendActionBarMessage("§7(You) §cRED: ".PlayerManager::$playerPoints[$player->getName()]." §7| §bBLUE: ".PlayerManager::$playerPoints[$opo]);

                                            $obj = new Position(549, 60, 1858, $player->getLevel());
                                            $selfobj = new Position(549, 60, 1802, $player->getLevel());

                                            if($player->distance($selfobj) < 4)
                                            {
                                                $player->teleport(new Location(549.5000, 74, 1801.5000, 256.5000, 1.4, $player->getLevel()));
                                            }

                                            if($player->distance($obj) < 4)
                                            {
                                                if(!isset(PlayerManager::$finished[$player->getName()]))
                                                {
                                                    PlayerManager::$playerPoints[$player->getName()] = PlayerManager::$playerPoints[$player->getName()]+1;
                                                }

                                                if(PlayerManager::$playerPoints[$player->getName()] > 2)
                                                {
                                                    $duel = DuelsManager::getDuel($player->getName());

                                                    if(!is_null($duel))
                                                    {
                                                        if(!isset(PlayerManager::$finished[$player->getName()]))
                                                        {
                                                            PlayerManager::$finished[$player->getName()] = true;
                                                            $duel->setWinner($player->getName(), $opo);
                                                        }
                                                    }
                                                }else{
                                                    $player->teleport(new Location(549.5000, 74, 1801.5000, 256.5000, 1.4, $player->getLevel()));

                                                    $opoo = Server::getInstance()->getPlayer($opo);

                                                    if(!is_null($opoo))
                                                    {
                                                        $opoo->teleport(new Location(549.5000, 74, 1859.5000, 179.5000, 3.5, $player->getLevel()));
                                                        $opoo->setImmobile();
                                                        $player->setImmobile();
                                                        $player->sendMessage("§eThe Bridge §l» §r§a{$player->getDisplayName()} scored.");
                                                        $opoo->sendMessage("§eThe Bridge §l» §r§a{$player->getDisplayName()} scored.");
                                                        $player->getArmorInventory()->clearAll();
                                                        $opoo->getArmorInventory()->clearAll();
                                                        $player->getInventory()->clearAll();
                                                        $opoo->getInventory()->clearAll();

                                                        Main::getInstance()->getScheduler()->scheduleRepeatingTask(new BridgeScheduler($player->getName(), $opoo->getName()), 20);
                                                    }
                                                }
                                            }

                                            break;
                                        case "blue":

                                            $player->sendActionBarMessage("§cRED: ".PlayerManager::$playerPoints[$opo]." §7| (You) §bBLUE: ".PlayerManager::$playerPoints[$player->getName()]);

                                            $obj = new Position(549, 60, 1802, $player->getLevel());
                                            $selfobj = new Position(549, 60, 1858, $player->getLevel());

                                            if($player->distance($selfobj) < 4)
                                            {
                                                $player->teleport(new Location(549.5000, 74, 1859.5000, 179.5000, 3.5, $player->getLevel()));
                                            }

                                            if($player->distance($obj) < 4)
                                            {
                                                if(!isset(PlayerManager::$finished[$player->getName()]))
                                                {
                                                    PlayerManager::$playerPoints[$player->getName()] = PlayerManager::$playerPoints[$player->getName()]+1;
                                                }

                                                if(PlayerManager::$playerPoints[$player->getName()] > 2)
                                                {
                                                    $duel = DuelsManager::getDuel($player->getName());

                                                    if(!is_null($duel))
                                                    {
                                                        if(!isset(PlayerManager::$finished[$player->getName()]))
                                                        {
                                                            PlayerManager::$finished[$player->getName()] = true;
                                                            $duel->setWinner($player->getName(), $opo);
                                                        }
                                                    }
                                                }else{
                                                    $player->teleport(new Location(549.5000, 74, 1859.5000, 179.5000, 3.5, $player->getLevel()));

                                                    $opoo = Server::getInstance()->getPlayer($opo);

                                                    if(!is_null($opoo))
                                                    {
                                                        $opoo->teleport(new Location(549.5000, 74, 1801.5000, 256.5000, 1.4, $player->getLevel()));
                                                        $opoo->setImmobile();
                                                        $player->setImmobile();
                                                        $player->sendMessage("§eThe Bridge §l» §r§a{$player->getDisplayName()} scored.");
                                                        $opoo->sendMessage("§eThe Bridge §l» §r§a{$player->getDisplayName()} scored.");
                                                        $player->getArmorInventory()->clearAll();
                                                        $opoo->getArmorInventory()->clearAll();
                                                        $player->getInventory()->clearAll();
                                                        $opoo->getInventory()->clearAll();

                                                        Main::getInstance()->getScheduler()->scheduleRepeatingTask(new BridgeScheduler($opoo->getName(), $player->getName()), 20);
                                                    }
                                                }
                                            }
                                            break;
                                    }
                                }
                            }
                        }
                    }

                    if($player->getY() < 57)
                    {
                        if(isset(PlayerManager::$playerTeam[$player->getName()]))
                        {
                            switch(PlayerManager::$playerTeam[$player->getName()])
                            {
                                case "red":
                                    $player->teleport(new Location(549.5000, 74, 1801.5000, 256.5000, 1.4, $player->getLevel()));
                                    break;
                                case "blue":
                                    $player->teleport(new Location(549.5000, 74, 1859.5000, 179.5000, 3.5, $player->getLevel()));
                                    break;
                            }
                        }

                        KitsAPI::addTheBridgeKit($player);
                    }
                }
            }
        }

        date_default_timezone_set('Europe/Paris');
        $hour = date("H");
        $minute = date("i");
        $second = date("s");

        $region = InformationAPI::getServerRegion();

        switch($region)
        {
            case "EU":
                if($hour == 03 && $minute == 00 && $second < 03)
                {
                    foreach (Server::getInstance()->getOnlinePlayers() as $player)
                    {
                        $player->transfer("ectary.club", 19132);
                    }

                    Server::getInstance()->shutdown();

                    register_shutdown_function(function () {
                        pcntl_exec("./start.sh");
                    });
                }
                if($hour == 15 && $minute == 00 && $second < 03)
                {
                    foreach (Server::getInstance()->getOnlinePlayers() as $player)
                    {
                        $player->transfer("ectary.club", 19132);
                    }

                    Server::getInstance()->shutdown();

                    register_shutdown_function(function () {
                        pcntl_exec("./start.sh");
                    });
                }
                break;
            case "NA":
            case "SA":
                if($hour == 9 && $minute == 00 && $second < 03)
                {
                    foreach (Server::getInstance()->getOnlinePlayers() as $player)
                    {
                        $player->transfer("ectary.club", 19132);
                    }

                    Server::getInstance()->shutdown();

                    register_shutdown_function(function () {
                        pcntl_exec("./start.sh");
                    });
                }
                if($hour == 21 && $minute == 00 && $second < 03)
                {
                    foreach (Server::getInstance()->getOnlinePlayers() as $player)
                    {
                        $player->transfer("ectary.club", 19132);
                    }

                    Server::getInstance()->shutdown();

                    register_shutdown_function(function () {
                        pcntl_exec("./start.sh");
                    });
                }
                break;
            case "AS":
            case "AU":
                if($hour == 18 && $minute == 00 && $second < 03)
                {
                    foreach (Server::getInstance()->getOnlinePlayers() as $player)
                    {
                        $player->transfer("ectary.club", 19132);
                    }

                    Server::getInstance()->shutdown();

                    register_shutdown_function(function () {
                        pcntl_exec("./start.sh");
                    });
                }
                if($hour == 06 && $minute == 00 && $second < 03)
                {
                    foreach (Server::getInstance()->getOnlinePlayers() as $player)
                    {
                        $player->transfer("ectary.club", 19132);
                    }

                    Server::getInstance()->shutdown();

                    register_shutdown_function(function () {
                        pcntl_exec("./start.sh");
                    });
                }
                break;
            default:
                break;
        }
    }
}

class BridgeScheduler extends Task
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
                KitsAPI::addTheBridgeKit($playerRed);
                KitsAPI::addTheBridgeKit($playerBlue);
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

class HikabrainScheduler extends Task
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
                KitsAPI::addHikabrainKit($playerRed);
                KitsAPI::addHikabrainKit($playerBlue);
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

                if($this->timer == 3)
                {
                    if(isset(BlockPlace::$blocks[$this->playerRed]))
                    {
                        $blockss = BlockPlace::$blocks[$this->playerRed];
                        foreach ($blockss as $block)
                        {
                            $b = explode(':', $block);
                            $playerRed->getLevel()->setBlock(new Vector3($b['0'], $b['1'], $b['2']), Block::get(0));
                        }
                    }

                    if(isset(BlockPlace::$blocks[$this->playerBlue]))
                    {
                        $blockss = BlockPlace::$blocks[$this->playerBlue];
                        foreach($blockss as $block)
                        {
                            $b = explode(':', $block);
                            $playerBlue->getLevel()->setBlock(new Vector3($b['0'], $b['1'], $b['2']), Block::get(0));
                        }
                    }
                }
            }
        }else{
            Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
        }
    }
}