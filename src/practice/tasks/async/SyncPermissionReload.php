<?php


namespace practice\tasks\async;


use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use practice\api\PlayerDataAPI;
use practice\manager\SQLManager;
use practice\provider\PermissionProvider;

class SyncPermissionReload extends AsyncTask
{
    private $player;

    public function __construct($player)
    {
        $this->player = $player;
    }

    public function onRun()
    {
        $db = SQLManager::getSQLSesionAsync();
        $prep = $db->prepare('SELECT `permissions` FROM `players` WHERE name = "' . $this->player . '"');
        $prep->execute();
        $result = $prep->get_result()->fetch_array(MYSQLI_ASSOC);
        $this->setResult($result);
        $db->close();
    }

    public function onCompletion(Server $server)
    {
        $permissions = $this->getResult()["permissions"];
        if (isset(SQLmanager::$cache[$this->player])) {
            Server::getInstance()->getLogger()->info("[Practice] " . $this->player . "  permissions reloaded. (" . PlayerDataAPI::getStringPermissions($this->player) . ") to ($permissions).");
            SQLmanager::$cache[$this->player]["permissions"] = $permissions;
            PermissionProvider::setPlayerPermission($this->player);
        }
    }
}