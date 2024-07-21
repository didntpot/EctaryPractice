<?php

namespace practice\web;

use pocketmine\Thread;

class WebThread extends Thread
{

    private $port;

    public function __construct($port)
    {
        $this->port = $port;
    }

    public function run()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            //shell_exec("php -S 127.0.0.1:" . $this->port . " -t " . str_replace("\\", "/", __DIR__) . "/");
        } else {
            //shell_exec("php -S 51.178.84.222:" . $this->port . " -t " . str_replace("\\", "/", __DIR__) . "/ 2> /dev/null");
        }
    }
}