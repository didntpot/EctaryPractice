<?php

namespace practice\events\listener;

use practice\Main;
use practice\manager\PlayerManager;
use pocketmine\event\Listener;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\entity\projectile\EnderPearl;
use pocketmine\scheduler\Task;
use pocketmine\Player;
use pocketmine\item\Item;

class ProjectileLaunch implements Listener
{
    public function onProjectileLaunch(ProjectileLaunchEvent $event)
    {
        $entity = $event->getEntity();
        $player = $entity->getOwningEntity();

        if($player->getLevel()->getName() == "kitroom")
        {
            $event->setCancelled();
        }

        if ($player instanceof Player && $entity instanceof EnderPearl) {
            if (!isset(PlayerManager::$pearl_time[$player->getName()])) {
                PlayerManager::$pearl_time[$player->getName()] = 10;
                Main::getInstance()->getScheduler()->scheduleRepeatingTask(new PearlTask($player), 20);
                Main::getInstance()->getScheduler()->scheduleRepeatingTask(new XPProgress($player), 2);
            } else {
                $event->setCancelled();
                PlayerManager::$need_pearl[$player->getName()] = true;
                Main::getInstance()->getScheduler()->scheduleRepeatingTask(new PearlGive($player), 1);
            }
        }
    }
}

class PearlTask extends Task
{
    private $player;

    public function __construct($player)
    {
        $this->player = $player;
    }

    public function onRun(int $currentTick)
    {
        if ($this->player->isOnline()) {
            if (isset(PlayerManager::$pearl_time[$this->player->getName()])) {
                PlayerManager::$pearl_time[$this->player->getName()]--;

                if (PlayerManager::$pearl_time[$this->player->getName()] !== 0) {
                    $this->player->setXpLevel(PlayerManager::$pearl_time[$this->player->getName()]);
                } else {
                    $this->player->setXpLevel(0);
                    unset(PlayerManager::$pearl_time[$this->player->getName()]);
                    Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
                }
            } else {
                $this->player->setXpLevel(0);
                Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
            }
        } else {
            unset(PlayerManager::$pearl_time[$this->player->getName()]);
            Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
        }
    }
}

class XPProgress extends Task
{
    private $timer = 100;
    private $player;

    public function __construct($player)
    {
        $this->player = $player;
    }

    public function onRun(int $currentTick)
    {
        if ($this->player->isOnline())
        {
            if(isset(PlayerManager::$pearl_time[$this->player->getName()]))
            {
                if($this->timer == 0)
                {
                    Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
                }else{
                    $this->timer--;
                    $percent = floatval($this->timer / 100);
                    $this->player->setXpProgress($percent);
                }
            }else{
                $this->player->setXpProgress(0);
                Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
            }
        }
    }
}

class PearlGive extends Task
{
    private $player;

    public function __construct($player)
    {
        $this->player = $player;
    }

    public function onRun(int $currentTick)
    {
        if ($this->player->isOnline()) {
            if (isset(PlayerManager::$need_pearl[$this->player->getName()])) {
                $this->player->getInventory()->addItem(Item::get(368, 0, 1));
                unset(PlayerManager::$need_pearl[$this->player->getName()]);
                Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
            } else {
                Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
            }
        } else {
            unset(PlayerManager::$need_pearl[$this->player->getName()]);
            Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
        }
    }
}