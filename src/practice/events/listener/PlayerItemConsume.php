<?php

namespace practice\events\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;

class PlayerItemConsume implements Listener
{
    public function onItemConsume(PlayerItemConsumeEvent $event)
    {
        $player = $event->getPlayer();
        $item = $event->getItem();
        $name = $item->getName();

        if($name == "§r§eGolden Head")
        {
            $player->addEffect(new EffectInstance(Effect::getEffect(10), 190 * 1, 1, false));
        }

        if($name == "§r§eHeal Apple")
        {
            $player->setHealth(20);
        }
    }
}