<?php


namespace practice\commands;


use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use practice\manager\SQLManager;
use practice\provider\WorkerProvider;

class Unban extends PluginCommand
{
    public function __construct(Plugin $owner)
    {
        parent::__construct("unban", $owner);
        $this->setPermission("unban.command");
        $this->setDescription("Unban command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if ($sender->hasPermission($this->getPermission())) {
            if (!isset($args[0])) return $sender->sendMessage("§c» Enter name.");
            $name = (!is_null(Server::getInstance()->getPlayer($args[0]))) ? Server::getInstance()->getPlayer($args[0])->getName() : $args[0];
            SQLManager::sendToWorker(new UnbanAsync($sender->getName(), $name), WorkerProvider::COMMAND_ASYNC);
        }
    }
}

class UnbanAsync extends AsyncTask
{

    private string $owner;
    private string $ban_name;

    public function __construct($owner, $ban_name)
    {
        $this->owner = $owner;
        $this->ban_name = $ban_name;
    }

    public function onRun()
    {
        $db = SQLManager::getSQLSesionAsync();
        $b = 0;
        $result = $db->query('SELECT * FROM `bans` WHERE `ban_name` = "' . $this->ban_name . '"')->fetch_all(MYSQLI_ASSOC);
        if (is_null($result)) {
            $db->close();
            return $this->setResult(["type" => "no_ban"]);
        }

        foreach ($result as $ban)
            if ($ban["unban"] == 1) {
                continue;
            } else if ((bool)$ban["ban_type"] == 0) {
                if ($ban["ban_expire"] >= time()) {
                    $db->query('UPDATE `bans` SET `unban`= 1, `unban_name`= "' . $this->owner . '" WHERE `ban_id` = "' . $ban["ban_id"] . '"');
                    $b++;
                } else {
                    continue;
                }
            } else {
                $db->query('UPDATE `bans` SET `unban`= 1, `unban_name`= "' . $this->owner . '" WHERE `ban_id` = "' . $ban["ban_id"] . '"');
                $b++;
                continue;
            }
        if ($b !== 0) {
            $db->close();
            return $this->setResult(["type" => "unban"]);
        }


        $this->setResult(["type" => "no_ban"]);
        $db->close();
    }

    public function onCompletion(Server $server)
    {
        switch ($this->getResult()["type"]) {
            case "no_ban":
                $player = Server::getInstance()->getPlayer($this->owner);
                if ($this->owner === "CONSOLE") Server::getInstance()->getLogger()->info("§c» " . $this->ban_name . " not banned.");
                if (!is_null($player)) $player->sendMessage("§c» " . $this->ban_name . " not banned.");
                break;
            case "unban":
                $player = Server::getInstance()->getPlayer($this->owner);
                if ($this->owner === "CONSOLE") Server::getInstance()->getLogger()->info("§c» " . $this->ban_name . " unbanned.");
                if (!is_null($player)) $player->sendMessage("§c» " . $this->ban_name . " unbanned.");
                break;
        }
    }
}