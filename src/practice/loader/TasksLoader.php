<?php

namespace practice\loader;

use practice\api\InformationAPI;
use practice\Main;
use practice\tasks\{Player, ModsHud, DivisionRankup, ReloadLeaderboard, StarterTask, Vanish, KitRoomTask, ScoreboardTask};
use practice\manager\TempRankTask;
use practice\manager\TempRankTaskAsync;
use practice\game\event\tasks\{
    WaitingTask,
    TimerTask
};
use practice\game\koth\KOTHTask;
use pocketmine\Server;
use practice\tasks\server\Announcement;
use practice\tasks\server\DayTask;
use practice\game\events\tasks\GameTask;

class TasksLoader extends Main
{
    public static function initTasks()
    {
        Main::getInstance()->getScheduler()->scheduleRepeatingTask(new Player(), 20);
        Main::getInstance()->getScheduler()->scheduleRepeatingTask(new KOTHTask(), 20);
        Main::getInstance()->getScheduler()->scheduleRepeatingTask(new StarterTask(), 19);
        Main::getInstance()->getScheduler()->scheduleRepeatingTask(new Announcement(), 2000);
        Main::getInstance()->getScheduler()->scheduleRepeatingTask(new DayTask(), 1200);
        Main::getInstance()->getScheduler()->scheduleRepeatingTask(new ModsHud(), 10);
        Main::getInstance()->getScheduler()->scheduleRepeatingTask(new GameTask(), 20);
        #Main::getInstance()->getScheduler()->scheduleRepeatingTask(new TimerTask(), 20);
        Main::getInstance()->getScheduler()->scheduleRepeatingTask(new DivisionRankup(), 3);
        Main::getInstance()->getScheduler()->scheduleRepeatingTask(new Vanish(), 20);
        Main::getInstance()->getScheduler()->scheduleRepeatingTask(new ReloadLeaderboard(), 12000);
        Main::getInstance()->getScheduler()->scheduleRepeatingTask(new TempRankTask(), 1200);
        Main::getInstance()->getScheduler()->scheduleRepeatingTask(new KitRoomTask(), 1);

        if (Server::getInstance()->getPort() == 19132) {
            Main::getInstance()->getScheduler()->scheduleRepeatingTask(new TempRankTaskAsync(), 12000);
        }
    }
}