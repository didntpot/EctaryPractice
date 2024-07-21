<?php


namespace practice\api;


use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\Player;

class SoundAPI
{

    public static function playSound($player, $name, $volume = 1)
    {
        if (!is_null($player))
        {
            $pk = new PlaySoundPacket();
            $pk->x = $player->getX();
            $pk->y = $player->getY();
            $pk->z = $player->getZ();
            $pk->volume = $volume;
            $pk->pitch = 1;
            $pk->soundName = $name;
            $player->dataPacket($pk);
        }
    }
}