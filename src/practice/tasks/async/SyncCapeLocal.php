<?php


namespace practice\tasks\async;


use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use practice\manager\CapesManager;
use practice\manager\SQLManager;
use practice\Main;

class SyncCapeLocal extends AsyncTask
{

    public function onRun()
    {
        $db = SQLManager::getSQLSesionAsync();
        $db->set_charset("utf8");
        $prep = $db->prepare("SELECT `cape_bytes`, `cape_name`, `permission`, `cape_image_link` FROM `capes`");
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
                CapesManager::setCacheCape($this->getResult()["result"]);

                foreach ($this->getResult()["result"] as $bytes) {
                    self::skinDataToImage($bytes["cape_bytes"], $bytes["cape_name"]);
                }

                break;
            case "error":
                Server::getInstance()->getLogger()->warning("Error capes: " . $this->getResult()["result"]);
                break;
        }
    }


    public static function skinDataToImage(string $skinData, $name)
    {
        $size = strlen($skinData);
        $patch = Main::getInstance()->getDataFolder() . "/$name.png";
        $width = 64;
        $height = 32;
        $skinPos = 0;
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $r = ord($skinData[$skinPos]);
                $skinPos++;
                $g = ord($skinData[$skinPos]);
                $skinPos++;
                $b = ord($skinData[$skinPos]);
                $skinPos++;
                $a = 127 - intdiv(ord($skinData[$skinPos]), 2);
                $skinPos++;
                $col = imagecolorallocatealpha($image, $r, $g, $b, $a);
                imagesetpixel($image, $x, $y, $col);
            }
        }
        imagepng($image, $patch);
        imagesavealpha($image, true);
        return $image;
    }
}