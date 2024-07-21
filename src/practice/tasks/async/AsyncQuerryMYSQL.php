<?php


namespace practice\tasks\async;


use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use practice\manager\LogsManager;
use practice\manager\SQLManager;

class AsyncQuerryMYSQL extends AsyncTask
{

    private $querry;

    public function __construct($querry)
    {
        $this->querry = $querry;
    }

    public function onRun()
    {
        $db = SQLManager::getSQLSesionAsync();
        $prep = $db->prepare($this->querry);
        if (is_bool($prep)) return $this->setResult($db->error);
        $prep->execute();
        $db->close();
        $this->setResult($this->querry);
    }

    public function onCompletion(Server $server)
    {
        if (LogsManager::isLogs()) Server::getInstance()->getLogger()->info("[Practice] QUERY SQL: " . $this->getResult());
    }
}