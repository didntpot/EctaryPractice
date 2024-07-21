<?php

namespace practice\tasks\server;

use pocketmine\scheduler\Task;
use pocketmine\Server;

class Announcement extends Task
{

    public static $message = [
        "§c§l» §l§bDid you know you could be a part of our community, get network updates, participate in our giveaways, and so much more by simply joining our discord server: discord.gg/ectary",
        "§c§l» §l§bWould you like to disguise as someone else, and hide your identity, or get custom tags, custom blocks and furthermore cosmetics? Head over to our store: https://store.ectary.club/category/ranks!",
        "§c§l» §l§bDo you wish to create your OWN nodebuff, gapple, sumo EVENT? Head over to our store and get yourself a suitable rank: https://store.ectary.club/category/ranks!"];
    public static $after = 0;

    public function onRun(int $currentTick)
    {
        self::$after++;
        if (self::$after >= count(self::$message)) self::$after = 0;
        Server::getInstance()->broadcastMessage(self::$message[self::$after]);
    }
}