<?php

namespace practice\events\listener;

use pocketmine\event\Listener;
use pocketmine\event\entity\EntityShootBowEvent;
use practice\duels\manager\DuelsManager;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use practice\Main;
use pocketmine\Server;

class EntityShootBow implements Listener
{
    public static $cooldown = [];

    public function onEntityShootBow(EntityShootBowEvent $event)
    {
        $player = $event->getEntity();
        $event->setForce($event->getForce() * 1.5);

        if($player instanceof Player)
        {
            if((DuelsManager::isInDuel($player->getName()) && !is_null(DuelsManager::getDuel($player->getName())) && DuelsManager::getDuel($player->getName())->getKit() === "thebridge"))
            {
                if(isset(self::$cooldown[$player->getName()]))
                {
                    $event->setCancelled();
                    $player->sendMessage("§c» Your bow is currently in cooldown for §l".$player->getXpLevel()."§r§c seconds.");
                }else{
                    self::$cooldown[$player->getName()] = true;
                    Main::getInstance()->getScheduler()->scheduleRepeatingTask(new BowTask($player->getName()), 20);
                }
            }
        }
    }
}

class BowTask extends Task
{
    public $timer = 5;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function onRun(int $currentTick)
    {
        $player = Server::getInstance()->getPlayer($this->name);

        if(!is_null($player))
        {
            if($this->timer == 0)
            {
                $player->setXpLevel($this->timer);
                unset(EntityShootBow::$cooldown[$this->name]);
                Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
            }else{
                $player->setXpLevel($this->timer);
                $this->timer--;
            }
        }else{
            unset(EntityShootBow::$cooldown[$this->name]);
            Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
        }
    }
}