<?php


namespace practice\api;


use pocketmine\entity\Entity;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\Server;
use pocketmine\Player;

class LightningAPI
{

    public static function spawnLightning(Position $position, Player $player)
    {
        if(PlayerDataAPI::getSetting($player->getName(), "lightning_death") === "true")
        {
            $light = new AddActorPacket();
            $light->type = "minecraft:lightning_bolt";
            $light->entityRuntimeId = Entity::$entityCount++;
            $light->metadata = [];
            $light->motion = null;
            $light->yaw = 1;
            $light->pitch = 1;
            $light->position = new Vector3($position->getX(), $position->getY(), $position->getZ());
            Server::getInstance()->broadcastPacket([$player], $light);
        }
    }
}