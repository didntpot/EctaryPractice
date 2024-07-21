<?php


namespace practice\duels\Task;


use pocketmine\scheduler\AsyncTask;

class Delete extends AsyncTask
{
    private $patch;

    public function __construct($patch)
    {
        $this->patch = $patch;
    }

    public function onRun()
    {
        $sys = strtoupper(substr(PHP_OS, 0, 3));

        if ($sys === "WIN"){
            self::delete($this->patch);
        }else{
            shell_exec("rm -R ". $this->patch);
        }
    }

    public static function delete(string $directory): bool
    {
        if (is_dir($directory)) {
            $objects = scandir($directory);

            foreach ($objects as $object) {
                if ($object !== "." and $object !== "..") {
                    if (is_dir($directory . "/" . $object)) {
                        self::delete($directory . "/" . $object);
                    } else {
                        unlink($directory . "/" . $object);
                    }
                }
            }

            rmdir($directory);
            return true;
        }else{
            return false;
        }
    }
}