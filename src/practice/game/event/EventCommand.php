<?php

namespace practice\game\event;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use practice\Main;
use practice\api\PlayerDataAPI;
use pocketmine\Server;
use pocketmine\level\Position;
use pocketmine\utils\Config;
use practice\manager\PlayerManager;

class EventCommand extends PluginCommand
{
    public function __construct($plugin)
    {
        parent::__construct("event", $plugin);
        $this->setDescription("Event command");
    }

    public function execute(CommandSender $player, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            $player->sendMessage("§c» Invalid syntax, please use '/event <join|start>'.");
        } else {
            switch ($args[0]) {
                case "join":
                    if (Manager::$is_running === true) {
                        if (Manager::$is_started === true) return $player->sendMessage("§c» The event has already started.");
                        if (isset(Manager::$is_ingame[$player->getName()])) {
                            $player->sendMessage("§c» You've already joined the event.");
                        } else {
                            Manager::addToGame($player);
                            $player->sendMessage("§a» You've joined the event.");
                            Server::getInstance()->broadcastMessage("§a» {$player->getDisplayName()} has joined the event. §7[" . Manager::$player_count . "]");
                            $player->getInventory()->clearAll();
                            $player->getArmorInventory()->clearAll();
                            $player->removeAllEffects();
                        }
                    } elseif (Manager::$is_running === false) {
                        $player->sendMessage("§c» There is no event currently running.");
                    }
                    break;
                case "start":
                    if ($player->hasPermission("event.start.command")) {
                        if (isset($args[1])) {
                            switch ($args[1]) {
                                case "nodebuff":
                                    if (Manager::$is_running === true) {
                                        $player->sendMessage("§c» An event is already started.");
                                    } elseif (Manager::$is_running === false) {
                                        Manager::$event_mode = "nodebuff";
                                        $config = new Config(str_replace("\\", "/", Main::getInstance()->getDataFolder() . "players/" . $player->getLowerCaseName() . ".yml"), Config::YAML);
                                        if (PlayerManager::getInformation($player->getName(), "group") === "Veteran") {
                                            if ($config->get("eventcooldown") >= time()) {
                                                $player->sendMessage("§c» This command have a 1 hour cooldown. (Higher ranks have less cooldown)");
                                            } else {
                                                $config->set("eventcooldown", time() + 3600);
                                                $config->save();
                                                Manager::setGame(true);
                                                $player->sendMessage("§a» You've started an event.");
                                                Manager::addToGame($player);
                                                Manager::$waiting = true;
                                                Server::getInstance()->broadcastMessage("§a» {$player->getName()} has started a nodebuff event, type §l'/event join'§r§a to join the event.");
                                            }
                                        } elseif (PlayerManager::getInformation($player->getName(), "group") === "Myth") {
                                            if ($config->get("eventcooldown") >= time()) {
                                                $player->sendMessage("§c» This command have a 30 minutes cooldown. (Higher ranks have less cooldown)");
                                            } else {
                                                $config->set("eventcooldown", time() + 1800);
                                                $config->save();
                                                Manager::setGame(true);
                                                $player->sendMessage("§a» You've started an event.");
                                                Manager::addToGame($player);
                                                Manager::$waiting = true;
                                                Server::getInstance()->broadcastMessage("§a» {$player->getName()} has started a nodebuff event, type §l'/event join'§r§a to join the event.");
                                            }
                                        } elseif (PlayerManager::getInformation($player->getName(), "group") === "Legend") {
                                            if ($config->get("eventcooldown") >= time()) {
                                                $player->sendMessage("§c» This command have a 15 minutes cooldown. (Higher ranks have less cooldown)");
                                            } else {
                                                $config->set("eventcooldown", time() + 900);
                                                $config->save();
                                                Manager::setGame(true);
                                                $player->sendMessage("§a» You've started an event.");
                                                Manager::addToGame($player);
                                                Manager::$waiting = true;
                                                Server::getInstance()->broadcastMessage("§a» {$player->getName()} has started a nodebuff event, type §l'/event join'§r§a to join the event.");
                                            }
                                        } elseif (PlayerManager::getInformation($player->getName(), "group") === "Booster") {
                                            if ($config->get("eventcooldown") >= time()) {
                                                $player->sendMessage("§c» This command have a 1 day cooldown. (Higher ranks have less cooldown)");
                                            } else {
                                                $config->set("eventcooldown", time() + 86400);
                                                $config->save();
                                                Manager::setGame(true);
                                                $player->sendMessage("§a» You've started an event.");
                                                Manager::addToGame($player);
                                                Manager::$waiting = true;
                                                Server::getInstance()->broadcastMessage("§a» {$player->getName()} has started a nodebuff event, type §l'/event join'§r§a to join the event.");
                                            }
                                        } elseif (PlayerManager::getInformation($player->getName(), "group") === "Youtube")
                                        {
                                            if ($config->get("eventcooldown") >= time()) {
                                                $player->sendMessage("§c» This command have a 1 day cooldown.");
                                            } else {
                                                $config->set("eventcooldown", time() + 86400);
                                                $config->save();
                                                Manager::setGame(true);
                                                $player->sendMessage("§a» You've started an event.");
                                                Manager::addToGame($player);
                                                Manager::$waiting = true;
                                                Server::getInstance()->broadcastMessage("§a» {$player->getName()} has started a nodebuff event, type §l'/event join'§r§a to join the event.");
                                            }
                                        }else{
                                            if ($config->get("eventcooldown") >= time()) {
                                                $player->sendMessage("§c» This command have a 1 day cooldown.");
                                            } else {
                                                $config->set("eventcooldown", time() + 1);
                                                $config->save();
                                                Manager::setGame(true);
                                                $player->sendMessage("§a» You've started an event.");
                                                Manager::addToGame($player);
                                                Manager::$waiting = true;
                                                Server::getInstance()->broadcastMessage("§a» {$player->getName()} has started a nodebuff event, type §l'/event join'§r§a to join the event.");
                                            }
                                        }
                                    }
                                    break;
                                case "gapple":
                                    if (Manager::$is_running === true) {
                                        $player->sendMessage("§c» An event is already started.");
                                    } elseif (Manager::$is_running === false) {
                                        Manager::$event_mode = "gapple";
                                        $config = new Config(str_replace("\\", "/", Main::getInstance()->getDataFolder() . "players/" . $player->getLowerCaseName() . ".yml"), Config::YAML);
                                        if (PlayerManager::getInformation($player->getName(), "group") === "Veteran") {
                                            if ($config->get("eventcooldown") >= time()) {
                                                $player->sendMessage("§c» This command have a 1 hour cooldown. (Higher ranks have less cooldown)");
                                            } else {
                                                $config->set("eventcooldown", time() + 3600);
                                                $config->save();
                                                Manager::setGame(true);
                                                $player->sendMessage("§a» You've started an event.");
                                                Manager::addToGame($player);
                                                Manager::$waiting = true;
                                                Server::getInstance()->broadcastMessage("§a» {$player->getName()} has started a gapple event, type §l'/event join'§r§a to join the event.");
                                            }
                                        } elseif (PlayerManager::getInformation($player->getName(), "group") === "Myth") {
                                            if ($config->get("eventcooldown") >= time()) {
                                                $player->sendMessage("§c» This command have a 30 minutes cooldown. (Higher ranks have less cooldown)");
                                            } else {
                                                $config->set("eventcooldown", time() + 1800);
                                                $config->save();
                                                Manager::setGame(true);
                                                $player->sendMessage("§a» You've started an event.");
                                                Manager::addToGame($player);
                                                Manager::$waiting = true;
                                                Server::getInstance()->broadcastMessage("§a» {$player->getName()} has started a gapple event, type §l'/event join'§r§a to join the event.");
                                            }
                                        } elseif (PlayerManager::getInformation($player->getName(), "group") === "Legend") {
                                            if ($config->get("eventcooldown") >= time()) {
                                                $player->sendMessage("§c» This command have a 15 minutes cooldown. (Higher ranks have less cooldown)");
                                            } else {
                                                $config->set("eventcooldown", time() + 900);
                                                $config->save();
                                                Manager::setGame(true);
                                                $player->sendMessage("§a» You've started an event.");
                                                Manager::addToGame($player);
                                                Manager::$waiting = true;
                                                Server::getInstance()->broadcastMessage("§a» {$player->getName()} has started a gapple event, type §l'/event join'§r§a to join the event.");
                                            }
                                        } elseif (PlayerManager::getInformation($player->getName(), "group") === "Booster") {
                                            if ($config->get("eventcooldown") >= time()) {
                                                $player->sendMessage("§c» This command have a 1 day cooldown. (Higher ranks have less cooldown)");
                                            } else {
                                                $config->set("eventcooldown", time() + 86400);
                                                $config->save();
                                                Manager::setGame(true);
                                                $player->sendMessage("§a» You've started an event.");
                                                Manager::addToGame($player);
                                                Manager::$waiting = true;
                                                Server::getInstance()->broadcastMessage("§a» {$player->getName()} has started a gapple event, type §l'/event join'§r§a to join the event.");

                                            }
                                        } elseif (PlayerManager::getInformation($player->getName(), "group") === "Youtube")
                                        {
                                            if ($config->get("eventcooldown") >= time()) {
                                                $player->sendMessage("§c» This command have a 1 day cooldown.");
                                            } else {
                                                $config->set("eventcooldown", time() + 86400);
                                                $config->save();
                                                Manager::setGame(true);
                                                $player->sendMessage("§a» You've started an event.");
                                                Manager::addToGame($player);
                                                Manager::$waiting = true;
                                                Server::getInstance()->broadcastMessage("§a» {$player->getName()} has started a gapple event, type §l'/event join'§r§a to join the event.");
                                            }
                                        }else{
                                            if ($config->get("eventcooldown") >= time()) {
                                                $player->sendMessage("§c» This command have a 1 day cooldown.");
                                            } else {
                                                $config->set("eventcooldown", time() + 1);
                                                $config->save();
                                                Manager::setGame(true);
                                                $player->sendMessage("§a» You've started an event.");
                                                Manager::addToGame($player);
                                                Manager::$waiting = true;
                                                Server::getInstance()->broadcastMessage("§a» {$player->getName()} has started a gapple event, type §l'/event join'§r§a to join the event.");
                                            }
                                        }
                                    }
                                    break;
                                case "sumo":
                                    if (Manager::$is_running === true) {
                                        $player->sendMessage("§c» An event is already started.");
                                    } elseif (Manager::$is_running === false) {
                                        Manager::$event_mode = "sumo";
                                        $config = new Config(str_replace("\\", "/", Main::getInstance()->getDataFolder() . "players/" . $player->getLowerCaseName() . ".yml"), Config::YAML);
                                        if (PlayerManager::getInformation($player->getName(), "group") === "Veteran") {
                                            if ($config->get("eventcooldown") >= time()) {
                                                $player->sendMessage("§c» This command have a 1 hour cooldown. (Higher ranks have less cooldown)");
                                            } else {
                                                $config->set("eventcooldown", time() + 3600);
                                                $config->save();
                                                Manager::setGame(true);
                                                $player->sendMessage("§a» You've started an event.");
                                                Manager::addToGame($player);
                                                Manager::$waiting = true;
                                                Server::getInstance()->broadcastMessage("§a» {$player->getName()} has started a sumo event, type §l'/event join'§r§a to join the event.");
                                            }
                                        } elseif (PlayerManager::getInformation($player->getName(), "group") === "Myth") {
                                            if ($config->get("eventcooldown") >= time()) {
                                                $player->sendMessage("§c» This command have a 30 minutes cooldown. (Higher ranks have less cooldown)");
                                            } else {
                                                $config->set("eventcooldown", time() + 1800);
                                                $config->save();
                                                Manager::setGame(true);
                                                $player->sendMessage("§a» You've started an event.");
                                                Manager::addToGame($player);
                                                Manager::$waiting = true;
                                                Server::getInstance()->broadcastMessage("§a» {$player->getName()} has started a sumo event, type §l'/event join'§r§a to join the event.");
                                            }
                                        } elseif (PlayerManager::getInformation($player->getName(), "group") === "Legend") {
                                            if ($config->get("eventcooldown") >= time()) {
                                                $player->sendMessage("§c» This command have a 15 minutes cooldown. (Higher ranks have less cooldown)");
                                            } else {
                                                $config->set("eventcooldown", time() + 900);
                                                $config->save();
                                                Manager::setGame(true);
                                                $player->sendMessage("§a» You've started an event.");
                                                Manager::addToGame($player);
                                                Manager::$waiting = true;
                                                Server::getInstance()->broadcastMessage("§a» {$player->getName()} has started a sumo event, type §l'/event join'§r§a to join the event.");
                                            }
                                        } elseif (PlayerManager::getInformation($player->getName(), "group") === "Booster") {
                                            if ($config->get("eventcooldown") >= time()) {
                                                $player->sendMessage("§c» This command have a 1 day cooldown. (Higher ranks have less cooldown)");
                                            } else {
                                                $config->set("eventcooldown", time() + 86400);
                                                $config->save();
                                                Manager::setGame(true);
                                                $player->sendMessage("§a» You've started an event.");
                                                Manager::addToGame($player);
                                                Manager::$waiting = true;
                                                Server::getInstance()->broadcastMessage("§a» {$player->getName()} has started a sumo event, type §l'/event join'§r§a to join the event.");
                                            }
                                        } elseif (PlayerManager::getInformation($player->getName(), "group") === "Youtube")
                                        {
                                            if ($config->get("eventcooldown") >= time()) {
                                                $player->sendMessage("§c» This command have a 1 day cooldown.");
                                            } else {
                                                $config->set("eventcooldown", time() + 86400);
                                                $config->save();
                                                Manager::setGame(true);
                                                $player->sendMessage("§a» You've started an event.");
                                                Manager::addToGame($player);
                                                Manager::$waiting = true;
                                                Server::getInstance()->broadcastMessage("§a» {$player->getName()} has started a sumo event, type §l'/event join'§r§a to join the event.");
                                            }
                                        }else{
                                            if ($config->get("eventcooldown") >= time()) {
                                                $player->sendMessage("§c» This command have a 1 day cooldown.");
                                            } else {
                                                $config->set("eventcooldown", time() + 1);
                                                $config->save();
                                                Manager::setGame(true);
                                                $player->sendMessage("§a» You've started an event.");
                                                Manager::addToGame($player);
                                                Manager::$waiting = true;
                                                Server::getInstance()->broadcastMessage("§a» {$player->getName()} has started a sumo event, type §l'/event join'§r§a to join the event.");
                                            }
                                        }
                                    }
                                    break;
                            }
                        } else {
                            $player->sendMessage("§c» Please specify a gamemode (nodebuff, gapple, sumo).");
                        }
                    } else {
                        $player->sendMessage("§cYou do not have permission to use this command.");
                    }
                    break;
            }
        }
    }
}