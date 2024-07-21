<?php


namespace practice\loader;


use pocketmine\Server;
use pocketmine\utils\Config;

class WorkerLoader
{
    public static function initWorker()
    {
        $config = new Config(Server::getInstance()->getDataPath() . "pocketmine.yml", Config::YAML);
        $worker_number = $config->get("settings");
        if ($worker_number["async-workers"] != 6) {
            $worker_number["async-workers"] = 6;
            $config->set("settings", $worker_number);
            $config->save();
            Server::getInstance()->getLogger()->warning("[Practice] Worker number set to 6.");
            Server::getInstance()->shutdown();

            register_shutdown_function(function () {
                pcntl_exec("./start.sh");
            });
        }
    }
}