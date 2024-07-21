<?php

namespace practice\events\listener;

use practice\api\SoundAPI;
use practice\api\CpsAPI;
use practice\api\form\CustomForm;
use practice\api\PlayerDataAPI;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;

class DataPacketReceive implements Listener
{
    public function onDataReceive(DataPacketReceiveEvent $event)
    {
        $packet = $event->getPacket();
        $player = $event->getPlayer();

        if ($packet instanceof LevelSoundEventPacket)
        {
            $soundType = $packet->sound;
            if ($soundType === LevelSoundEventPacket::SOUND_ATTACK_NODAMAGE) $event->setCancelled();
            if ($soundType === LevelSoundEventPacket::SOUND_ATTACK_STRONG) $event->setCancelled();
        }
    }
}