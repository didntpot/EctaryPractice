<?php


namespace practice\manager;


use practice\web\WebThread;

const PORT = "8080";

class WebManager
{
    public static function startWeb()
    {
        $th = new WebThread(PORT);
        $th->start(PTHREADS_INHERIT_NONE);
        //echo "\e[36m[" . date("H:i:s") . "] [Web thread/INFO]: Listening on port 8080 For Connection...", PHP_EOL;
    }

    public static function stopWeb()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            //shell_exec("kill $(lsof -t -i :" . PORT . ")");
        }
    }
}