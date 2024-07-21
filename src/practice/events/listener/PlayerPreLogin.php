<?php


namespace practice\events\listener;


use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\LoginPacket;
use practice\manager\PlayerManager;
use practice\manager\SQLManager;

class PlayerPreLogin implements Listener
{
    public function onPacketReceived(DataPacketReceiveEvent $event)
    {
        $player = $event->getPlayer();

        $os = ["Unknown", "Android", "iOS", "macOS", "FireOS", "GearVR", "HoloLens", "Windows 10", "Windows", "Dedicated", "Orbis", "Playstation 4", "Nintento Switch", "Xbox One"];
        if ($event->getPacket() instanceof LoginPacket) {
            $device = $event->getPacket()->clientData['DeviceOS'];

            PlayerManager::$ip[$event->getPacket()->username] = $event->getPlayer()->getAddress();
            PlayerManager::$os[$event->getPacket()->username] = $os[$device];
            PlayerManager::$id_device[$event->getPacket()->username] = $event->getPacket()->clientData["DeviceId"];
        }
    }
}