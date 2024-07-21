<?php


namespace practice\commands;


use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use practice\manager\SQLManager;
use practice\provider\WorkerProvider;

class Alias extends PluginCommand
{
    public function __construct(Plugin $owner)
    {
        parent::__construct("alias", $owner);
        $this->setPermission("alias.command");
        $this->setDescription("Alias command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if(!$sender->hasPermission($this->getPermission())) return;
            if (!isset($args[0])) return $sender->sendMessage("§c» Command usage: /alias <player, id>");
                SQLManager::sendToWorker(new AliasCommandAsync($args[0], $sender->getName()), WorkerProvider::COMMAND_ASYNC);
    }
}

class AliasCommandAsync extends AsyncTask
{
    private $name;
    private $sender;

    public function __construct($name, $sender)
    {
        $this->name = $name;
        $this->sender = $sender;
    }

    public function onRun()
    {
        $db = SQLManager::getSQLSesionAsync();
        $get_player = $db->query('SELECT `name`, `id_device`, `proxy` FROM `players` WHERE `name` = "' . $this->name . '"');

        if (is_null($get_player))
        {
            $db->close();
            return $this->setResult(["type" => "error", "msg" => "Player no found"]);
        }

        if (empty($db->error)) {
            $id = $get_player->fetch_array(MYSQLI_ASSOC);
            $id_device = (empty($id["id_device"])) ? '': 'OR `id_device` = "' . $id["id_device"] . '"';
            $all_player = $db->query('SELECT `name`,`id_device`, `proxy` FROM `players` WHERE `name` = "' . $this->name . '"'. $id_device)->fetch_all(MYSQLI_ASSOC);
            if (empty($all_player))
            {
                $db->close();
                return $this->setResult(["type" => "error", "msg" => "Player no found"]);
            }
            $this->setResult(["type" => "good", "players" => $all_player]);
        }
        $db->close();
    }

    public function onCompletion(Server $server)
    {
        $player = Server::getInstance()->getPlayer($this->sender);

        switch ($this->getResult()["type"]) {
            case "error":
                if (!is_null($player)) {
                    $player->sendMessage("§c» The player couldn't been found.");
                } elseif ($this->sender === "CONSOLE") {
                    Server::getInstance()->getLogger()->info("§c» The player couldn't been found.");
                }
                break;
            case "good":
                if (!is_null($player)) {
                    $proxy = ($this->getResult()["players"][0]["proxy"] == 1) ? "Yes" : "No";
                    $player->sendMessage("§a» ID: " . $this->getResult()["players"][0]["id_device"] . "\n§a» Proxy: " . $proxy . "\n§a» IGNs:\n- " . implode("\n- ", array_column($this->getResult()["players"], "name")));

                } elseif ($this->sender === "CONSOLE") {
                    $proxy = ($this->getResult()["players"][0]["proxy"] == 1) ? "Yes" : "No";
                    Server::getInstance()->getLogger()->info("\n§a» ID: " . $this->getResult()["players"][0]["id_device"] . "\n§a» Proxy: " . $proxy . "\n§a» IGNs:\n- " . implode("\n- ", array_column($this->getResult()["players"], "name")));
                }
                break;
        }
    }
}