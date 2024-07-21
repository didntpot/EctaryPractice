<?php

namespace practice\events\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerBucketEvent;
use pocketmine\scheduler\Task;
use pocketmine\block\Block;
use practice\Main;

class PlayerBucket implements Listener
{
    public function onPlayerBucket(PlayerBucketEvent $event)
    {
        $player = $event->getPlayer();
        $blockClicked = $event->getBlockClicked();

        if($player->getLevel()->getName() == "buhc")
        {
            Main::getInstance()->getScheduler()->scheduleDelayedTask(new BucketTask($blockClicked), 160);
        }
    }
}

class BucketTask extends Task
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