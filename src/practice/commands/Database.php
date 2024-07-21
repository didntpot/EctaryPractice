<?php

namespace practice\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use practice\api\form\CustomForm;
use practice\Main;
use practice\manager\SQLManager;
use practice\provider\WorkerProvider;

class Database extends PluginCommand
{

    public function __construct($plugin)
    {
        parent::__construct("database", $plugin);
        $this->setDescription("Database command");
        $this->setPermission("database.command");
    }

    public function execute(CommandSender $player, string $commandLabel, array $args)
    {
        if ($player->hasPermission("database.command")) {
            SQLManager::sendToWorker(new DatabaseCommandAsync($player->getName()), WorkerProvider::COMMAND_ASYNC);
        }
    }
}

class DatabaseCommandAsync extends AsyncTask
{

    /**
     * @var string
     */
    private $player_name;

    public function __construct(string $player_name)
    {
        $this->player_name = $player_name;
    }

    public function onRun()
    {
        $db = SQLManager::getSQLSesionAsync();
        $startTime = microtime(true);
        $db->query("SELECT `id` FROM `players` LIMIT 1");
        $endTime = microtime(true);
        $ms_latency = round(($endTime - $startTime) * 1000, 2);
        $host_info = $db->host_info;
        $server_info = $db->server_info;
        $warning_count = $db->warning_count;
        $db->close();
        $this->setResult(["ping" => $ms_latency, "host_info" => $host_info, "server_info" => $server_info, "warning_count" => $warning_count]);
    }

    public function onCompletion(Server $server)
    {
        $player = Server::getInstance()->getPlayer($this->player_name);
        if (!empty($player)) {
            $form = new CustomForm(function (Player $player, $data) {
                if (empty($data)) return;
            });
            $form->setTitle("Database info");
            $form->addLabel("Ping response: " . $this->getResult()["ping"] . "ms");
            $form->addLabel("Host Info: " . $this->getResult()["host_info"]);
            $form->addLabel("Server Info: " . $this->getResult()["server_info"]);
            $form->addLabel("Warning Count: " . $this->getResult()["warning_count"]);
            $form->addLabel("Worker id: " . $this->worker->getAsyncWorkerId());
            $form->sendToPlayer($player);
        } else {
            Main::getInstance()->getLogger()->info("Database latence: " . $this->getResult()["ping"] . "ms");
        }
    }
}
