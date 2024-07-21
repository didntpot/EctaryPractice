<?php

namespace practice\game\event\tasks;

use pocketmine\scheduler\Task;
use practice\game\event\Manager;
use practice\api\KitsAPI;
use practice\Main;
use pocketmine\utils\Config;
use pocketmine\entity\{
    Effect,
    EffectInstance
};

class MatchStartTask extends Task
{
    public $timer = 3;

    public function __construct($playerOne, $playerTwo)
    {
        $this->playerOne = $playerOne;
        $this->playerTwo = $playerTwo;
    }

    public function onRun(int $currentTick)
    {
        $players = [$this->playerOne, $this->playerTwo];
        $config = new Config(Main::getInstance()->getDataFolder() . "event.json", Config::JSON);
        if (is_null($players[0])) {
            Manager::removeFromGame($players[0]->getName());
            $config->remove($players[0]->getName());
            $config->save();
        } elseif (is_null($players[1])) {
            Manager::removeFromGame($players[1]->getName());
            $config->remove($players[1]->getName());
            $config->save();
        } else {
            if ($this->timer === 0) {
                $players[0]->setImmobile(false);
                $players[1]->setImmobile(false);
                switch (Manager::$event_mode) {
                    case "nodebuff":
                        KitsAPI::addEventNodebuffKit($players[0]);
                        KitsAPI::addEventNodebuffKit($players[1]);
                        break;
                    case "gapple":
                        KitsAPI::addGappleKit($players[0]);
                        KitsAPI::addGappleKit($players[1]);
                        break;
                    case "sumo":
                        KitsAPI::addSumoKit($players[0]);
                        KitsAPI::addSumoKit($players[1]);
                        break;
                }
                Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
            } else {
                #$resistance = new EffectInstance(Effect::getEffect(Effect::RESISTANCE), 50 * 999999, 50, false);
                $this->timer--;
                $players[0]->sendPopup("§a» Match is starting in {$this->timer}s...");
                $players[1]->sendPopup("§a» Match is starting in {$this->timer}s...");
            }
        }
    }
}