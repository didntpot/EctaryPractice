<?php


namespace practice\tasks\async;


use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use practice\manager\SQLManager;
use practice\manager\TagsManager;

class SyncTagsLocal extends AsyncTask
{

    public function onRun()
    {
        $db = SQLManager::getSQLSesionAsync();
        $db->set_charset("utf8");
        $prep = $db->prepare("SELECT `tags_name`, `permission` FROM `tags`");
        if (empty($prep->error)) {
            $prep->execute();
            $tags = $prep->get_result()->fetch_all(MYSQLI_ASSOC);
            $this->setResult(["type" => "good", "result" => $tags]);
        } else {
            $this->setResult(["type" => "error", "result" => $prep->error]);
        }
        $db->close();
    }

    public function onCompletion(Server $server)
    {
        switch ($this->getResult()["type"]) {
            case "good":
                TagsManager::setCacheTags($this->getResult()["result"]);
                break;
            case "error":
                Server::getInstance()->getLogger()->warning("Error tags: " . $this->getResult()["result"]);
                break;
        }
    }
}