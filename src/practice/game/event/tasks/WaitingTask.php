<?php

namespace practice\game\event\tasks;

use pocketmine\scheduler\Task;
use practice\game\event\Manager;
use practice\Main;
use pocketmine\Server;
use pocketmine\utils\Config;

class WaitingTask extends Task
{
    public static $ok = false;

    public function onRun(int $currentTick)
    {
        if (Manager::$waiting === true) {
            $config = new Config(Main::getInstance()->getDataFolder() . "event.json", Config::JSON);
            $all = $config->getAll();
            foreach ($all as $name => $value) {
                $eplayer = Server::getInstance()->getPlayer($name);
                if (count($all) > 1) {
                    if (is_null($eplayer)) {
                        Manager::removeFromGame($name);
                        $config->remove($name);
                        $config->save();
                    } else {
                        Server::getInstance()->broadcastPopup("§a» The first match is starting in " . Manager::$waiting_timer . "s...");
                        self::$ok = true;
                    }
                } else {
                    if (is_null($eplayer)) {
                        Manager::removeFromGame($name);
                        $config->remove($name);
                        $config->save();
                    } else {
                        self::$ok = false;
                        $eplayer->sendPopup("§a» Waiting for more players...");
                    }
                }
            }
            if (Manager::$player_count === 0) {
                Manager::setGame(false);
            }
        }
    }
}

class TimerTask extends Task
{
    public function onRun(int $currentTick)
    {
        if (WaitingTask::$ok === true) {
            if (Manager::$waiting_timer === 0) {
                Manager::$waiting = false;
                Manager::$is_started = true;
                WaitingTask::$ok = false;
                Main::getInstance()->getScheduler()->scheduleRepeatingTask(new GameTask(), 20);
            } else {
                Manager::$waiting_timer--;
            }
        }
    }
}