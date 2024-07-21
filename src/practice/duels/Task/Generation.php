<?php

namespace practice\duels\Task;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use practice\duels\Duels;
use practice\duels\DuelsProvider;

class Generation extends AsyncTask
{

    private $patch;
    private $duel_id;
    private $map_name;
    private $world_patch;

    public function __construct($duel_id, $patch, $world_patch, $map_name)
    {
        $this->patch = $patch;
        $this->duel_id = $duel_id;
        $this->map_name = $map_name;
        $this->world_patch = $world_patch;
    }

    public function onRun()
    {
        if (is_dir($this->patch."/".$this->map_name))
        {
            if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN")
            {
                if (self::copyDir($this->patch."/".$this->map_name, $this->world_patch."/".$this->duel_id))
                {
                    $this->setResult(["type" => "ok"]);
                }else{
                    $this->setResult(["type" => "error", "message" => "World generation error"]);
                }
            }else{
                $source = $this->patch."/".$this->map_name;
                $dest = $this->world_patch."/".$this->duel_id;
                exec("cp -R $source $dest", $return, $return_var);

                if ($return_var == 0)
                {
                    $this->setResult(["type" => "ok"]);
                }else{
                    $this->setResult(["type" => "error", "message" => $return_var]);
                }
            }

        }else{
            $this->setResult(["type" => "error", "message" => "World no found (".$this->map_name.")"]);
        }
    }

    public function onCompletion(Server $server)
    {
        $duel = DuelsProvider::$duels[$this->duel_id];
        switch ($this->getResult()["type"])
        {
            case "error":
                if ($duel instanceof Duels)
                {
                    Server::getInstance()->getLogger()->info("Duel ERROR: ". $this->duel_id." -> ".$this->getResult()["message"]);
                    $duel->sendMessage("Error", "message");
                    $duel->stop();
                }
                break;
            case "ok":
                if ($duel instanceof Duels)
                {
                    Server::getInstance()->getLogger()->info($this->duel_id." generate.");
                    $duel->saveId();
                    $duel->setWorldGenerate(true);
                    $duel->startWait();
                }
                break;
        }
    }

    public static function copyDir($source, $destination)
    {
        if (!is_dir($source)) return false;
        $dir = opendir($source);
        @mkdir($destination);
        foreach (scandir($source) as $file) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($source . '/' . $file)) {
                    self::copyDir($source . '/' . $file, $destination . '/' . $file);
                } else {
                    copy($source . '/' . $file, $destination . '/' . $file);
                }
            }
        }
        closedir($dir);
        return true;
    }
}
