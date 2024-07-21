<?php

namespace practice\events\listener;

use pocketmine\level\particle\TerrainParticle;
use pocketmine\Player;
use practice\Main;
use practice\duels\manager\DuelsManager;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\scheduler\Task;
use pocketmine\block\Block;
use pocketmine\item\Item;

class BlockPlace implements Listener
{
    public function onBreak(BlockPlaceEvent $event)
    {
        $player = $event->getPlayer();
        $block = $event->getBlock();

        if($player->getLevel()->getName() == "kitroom")
        {
            $event->setCancelled();
        }

        if($player->getLevel()->getName() == "buhc")
        {
            if($block->getId() === 4)
            {
                if($block->getY() > 42)
                {
                    $event->setCancelled();
                    $player->sendMessage("§cYou can't build that high!");
                }else{
                    Main::getInstance()->getScheduler()->scheduleDelayedTask(new CobblestoneTask($block), 160);
                }
            }
        }

        if (self::isCanceled($player)) return;
        
        if($player->getLevel()->getName() == "spawn") return $event->setCancelled();
        if ($block->getId() === 24 or $block->getId() === 159) {
            if($player->getY() > 35 && $player->getLevel()->getName() === "build")
            {
                return $event->setCancelled();
            }
            if($player->getLevel()->getName() === "build")
            {
                Main::getInstance()->getScheduler()->scheduleRepeatingTask(new BlockFirstPhase($block), 20);
            }
            $player->getInventory()->setItemInHand(Item::get($player->getInventory()->getItemInHand()->getId(), $player->getInventory()->getItemInHand()->getDamage(), 64));
        }
        if($player->getLevel()->getName() == "build")
        {
            if ($block->getId() === 30) Main::getInstance()->getScheduler()->scheduleDelayedTask(new CobWebPhase($block), 100);
        }
    }

    public static function isCanceled(Player $player)
    {
        if (DuelsManager::isInDuel($player->getName())) return true;
    }

}

class BlockFirstPhase extends Task
{
    public $time = 20;
    /**
     * @var Block
     */
    private $block;

    public function __construct(Block $block)
    {
        $this->block = $block;
    }

    public function onRun(int $currentTick)
    {
        if ($this->block->getLevel()->getBlockIdAt($this->block->x, $this->block->y, $this->block->z) === Block::AIR) return Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
        if ($this->time == 0) {
            $this->block->getLevel()->setBlockIdAt($this->block->x, $this->block->y, $this->block->z, Block::AIR);
            Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
        } elseif ($this->time == 5) {
            $this->block->getLevel()->setBlockIdAt($this->block->x, $this->block->y, $this->block->z, Block::REDSTONE_BLOCK);
        }

        $this->time--;
    }
}

class CobblestoneTask extends Task
{
    public function __construct(Block $block)
    {
        $this->block = $block;
    }

    public function onRun(int $currentTick)
    {
        $this->block->getLevel()->setBlockIdAt($this->block->x, $this->block->y, $this->block->z, Block::AIR);
    }
}

class CobWebPhase extends Task
{
    /**
     * @var Block
     */
    private $block;

    public function __construct(Block $block)
    {
        $this->block = $block;
    }

    public function onRun(int $currentTick)
    {
        $this->block->getLevel()->setBlockIdAt($this->block->x, $this->block->y, $this->block->z, Block::AIR);
    }
}