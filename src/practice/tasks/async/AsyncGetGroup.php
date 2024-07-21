<?php


namespace practice\tasks\async;


use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use practice\api\SyncAPI;
use practice\manager\GroupManager;
use practice\manager\SQLManager;

class AsyncGetGroup extends AsyncTask
{

    public function onRun()
    {
        $db = SQLManager::getSQLSesionAsync();
        $db->set_charset("utf8");
        $prep = $db->prepare('SELECT `group_name`, `permissions`, `syntax` FROM `groups`');

        if (empty($db->error)) {
            $prep->execute();
            $result = $prep->get_result()->fetch_all(MYSQLI_ASSOC);
            $group = [];
            foreach ($result as $id => $group_data) {
                $group[$group_data["group_name"]] = ["permissions" => $group_data["permissions"], "syntax" => $group_data["syntax"]];
            }
            $this->setResult(["type" => "good", $group]);
        } else {
            $this->setResult(["type" => "error", $db->error]);
        }
        $db->close();
    }

    public function onCompletion(Server $server)
    {
        switch ($this->getResult()["type"]) {
            case "good":
                GroupManager::setGroupCache($this->getResult()[0]);
                break;
            case "error":
                break;
        }
    }
}