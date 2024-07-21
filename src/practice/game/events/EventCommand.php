<?php

namespace practice\game\events;

use practice\game\events\EventManager;
use pocketmine\Server;
use pocketmine\command\PluginCommand;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use practice\manager\PlayerManager;
use practice\Main;

class EventCommand extends PluginCommand
{
    public function __construct($plugin)
    {
        parent::__construct("event", $plugin);
        $this->setDescription("Event command");
    }

    public function execute(CommandSender $player, string $commandLabel, array $args)
    {
        if(!isset($args[0])) return $player->sendMessage("§cPlease use /event join|start nodebuff|gapple|sumo.");

        switch($args[0])
        {
            case "join":
                if(!isset(EventManager::$players[$player->getName()]))
                {
                    EventManager::add($player);
                }
                break;
            case "start":
                if($player->hasPermission("event.start.command"))
                {
                    if(!isset($args[1])) return $player->sendMessage("§cPlease use /event join|start nodebuff|gapple|sumo.");

                    $config = new Config(str_replace("\\", "/", Main::getInstance()->getDataFolder() . "players/" . $player->getLowerCaseName() . ".yml"), Config::YAML);

                    switch($args[1])
                    {
                        case "nodebuff":
                            if(EventManager::$isStarted == true) return $player->sendMessage("§c» An event is already started.");

                            switch(PlayerManager::getInformation($player->getName(), "group"))
                            {
                                case "Veteran":
                                    if($config->get("eventcooldown") >= time())
                                    {
                                        $player->sendMessage("§c» This command have a 1 hour cooldown. (Higher ranks have less cooldown)");
                                    }else{
                                        $config->set("eventcooldown", time() + 3600);
                                        $config->save();
                                        EventManager::start("nodebuff");
                                        EventManager::add($player);
                                        Server::getInstance()->broadcastMessage("§a» {$player->getName()} has started a nodebuff event, type /event join to join the event.");
                                    }
                                    break;
                                case "Myth":
                                    if($config->get("eventcooldown") >= time())
                                    {
                                        $player->sendMessage("§c» This command have a 30 hour cooldown. (Higher ranks have less cooldown)");
                                    }else{
                                        $config->set("eventcooldown", time() + 1800);
                                        $config->save();
                                        EventManager::start("nodebuff");
                                        EventManager::add($player);
                                        Server::getInstance()->broadcastMessage("§a» {$player->getName()} has started a nodebuff event, type /event join to join the event.");
                                    }
                                    break;
                                case "Legend":
                                    if($config->get("eventcooldown") >= time())
                                    {
                                        $player->sendMessage("§c» This command have a 10 minutes cooldown. (Higher ranks have less cooldown)");
                                    }else{
                                        $config->set("eventcooldown", time() + 900);
                                        $config->save();
                                        EventManager::start("nodebuff");
                                        EventManager::add($player);
                                        Server::getInstance()->broadcastMessage("§a» {$player->getName()} has started a nodebuff event, type /event join to join the event.");
                                    }
                                    break;
                                case "Booster":
                                    if($config->get("eventcooldown") >= time())
                                    {
                                        $player->sendMessage("§c» This command have a 1 day cooldown. (Higher ranks have less cooldown)");
                                    }else{
                                        $config->set("eventcooldown", time() + 86400);
                                        $config->save();
                                        EventManager::start("nodebuff");
                                        EventManager::add($player);
                                        Server::getInstance()->broadcastMessage("§a» {$player->getName()} has started a nodebuff event, type /event join to join the event.");
                                    }
                                    break;
                                case "Youtube":
                                    if($config->get("eventcooldown") >= time())
                                    {
                                        $player->sendMessage("§c» This command have a 1 day cooldown. (Higher ranks have less cooldown)");
                                    }else{
                                        $config->set("eventcooldown", time() + 86400);
                                        $config->save();
                                        EventManager::start("nodebuff");
                                        EventManager::add($player);
                                        Server::getInstance()->broadcastMessage("§a» {$player->getName()} has started a nodebuff event, type /event join to join the event.");
                                    }
                                    break;
                                case "Famous":
                                    if($config->get("eventcooldown") >= time())
                                    {
                                        $player->sendMessage("§c» This command have a 1 day cooldown. (Higher ranks have less cooldown)");
                                    }else{
                                        $config->set("eventcooldown", time() + 86400);
                                        $config->save();
                                        EventManager::start("nodebuff");
                                        EventManager::add($player);
                                        Server::getInstance()->broadcastMessage("§a» {$player->getName()} has started a nodebuff event, type /event join to join the event.");
                                    }
                                    break;
                                default:
                                    if($config->get("eventcooldown") >= time())
                                    {
                                        $player->sendMessage("§c» This command have a 10 minutes cooldown. (Higher ranks have less cooldown)");
                                    }else{
                                        $config->set("eventcooldown", time() + 900);
                                        $config->save();
                                        EventManager::start("nodebuff");
                                        EventManager::add($player);
                                        Server::getInstance()->broadcastMessage("§a» {$player->getName()} has started a nodebuff event, type /event join to join the event.");
                                    }
                                    break;
                            }
                            break;
                        case "gapple":
                            if(EventManager::$isStarted == true) return $player->sendMessage("§c» An event is already started.");

                            switch(PlayerManager::getInformation($player->getName(), "group"))
                            {
                                case "Veteran":
                                    if($config->get("eventcooldown") >= time())
                                    {
                                        $player->sendMessage("§c» This command have a 1 hour cooldown. (Higher ranks have less cooldown)");
                                    }else{
                                        $config->set("eventcooldown", time() + 3600);
                                        $config->save();
                                        EventManager::start("gapple");
                                        EventManager::add($player);
                                        Server::getInstance()->broadcastMessage("§a» {$player->getName()} has started a gapple event, type /event join to join the event.");
                                    }
                                    break;
                                case "Myth":
                                    if($config->get("eventcooldown") >= time())
                                    {
                                        $player->sendMessage("§c» This command have a 30 hour cooldown. (Higher ranks have less cooldown)");
                                    }else{
                                        $config->set("eventcooldown", time() + 1800);
                                        $config->save();
                                        EventManager::start("gapple");
                                        EventManager::add($player);
                                        Server::getInstance()->broadcastMessage("§a» {$player->getName()} has started a gapple event, type /event join to join the event.");
                                    }
                                    break;
                                case "Legend":
                                    if($config->get("eventcooldown") >= time())
                                    {
                                        $player->sendMessage("§c» This command have a 10 minutes cooldown. (Higher ranks have less cooldown)");
                                    }else{
                                        $config->set("eventcooldown", time() + 900);
                                        $config->save();
                                        EventManager::start("gapple");
                                        EventManager::add($player);
                                        Server::getInstance()->broadcastMessage("§a» {$player->getName()} has started a gapple event, type /event join to join the event.");
                                    }
                                    break;
                                case "Booster":
                                    if($config->get("eventcooldown") >= time())
                                    {
                                        $player->sendMessage("§c» This command have a 1 day cooldown. (Higher ranks have less cooldown)");
                                    }else{
                                        $config->set("eventcooldown", time() + 86400);
                                        $config->save();
                                        EventManager::start("gapple");
                                        EventManager::add($player);
                                        Server::getInstance()->broadcastMessage("§a» {$player->getName()} has started a gapple event, type /event join to join the event.");
                                    }
                                    break;
                                case "Youtube":
                                    if($config->get("eventcooldown") >= time())
                                    {
                                        $player->sendMessage("§c» This command have a 1 day cooldown. (Higher ranks have less cooldown)");
                                    }else{
                                        $config->set("eventcooldown", time() + 86400);
                                        $config->save();
                                        EventManager::start("gapple");
                                        EventManager::add($player);
                                        Server::getInstance()->broadcastMessage("§a» {$player->getName()} has started a gapple event, type /event join to join the event.");
                                    }
                                    break;
                                case "Famous":
                                    if($config->get("eventcooldown") >= time())
                                    {
                                        $player->sendMessage("§c» This command have a 1 day cooldown. (Higher ranks have less cooldown)");
                                    }else{
                                        $config->set("eventcooldown", time() + 86400);
                                        $config->save();
                                        EventManager::start("gapple");
                                        EventManager::add($player);
                                        Server::getInstance()->broadcastMessage("§a» {$player->getName()} has started a gapple event, type /event join to join the event.");
                                    }
                                    break;
                                default:
                                    if($config->get("eventcooldown") >= time())
                                    {
                                        $player->sendMessage("§c» This command have a 10 minutes cooldown. (Higher ranks have less cooldown)");
                                    }else{
                                        $config->set("eventcooldown", time() + 900);
                                        $config->save();
                                        EventManager::start("gapple");
                                        EventManager::add($player);
                                        Server::getInstance()->broadcastMessage("§a» {$player->getName()} has started a gapple event, type /event join to join the event.");
                                    }
                                    break;
                            }
                            break;
                        case "sumo":
                            if(EventManager::$isStarted == true) return $player->sendMessage("§c» An event is already started.");

                            switch(PlayerManager::getInformation($player->getName(), "group"))
                            {
                                case "Veteran":
                                    if($config->get("eventcooldown") >= time())
                                    {
                                        $player->sendMessage("§c» This command have a 1 hour cooldown. (Higher ranks have less cooldown)");
                                    }else{
                                        $config->set("eventcooldown", time() + 3600);
                                        $config->save();
                                        EventManager::start("sumo");
                                        EventManager::add($player);
                                        Server::getInstance()->broadcastMessage("§a» {$player->getName()} has sumo a nodebuff event, type /event join to join the event.");
                                    }
                                    break;
                                case "Myth":
                                    if($config->get("eventcooldown") >= time())
                                    {
                                        $player->sendMessage("§c» This command have a 30 hour cooldown. (Higher ranks have less cooldown)");
                                    }else{
                                        $config->set("eventcooldown", time() + 1800);
                                        $config->save();
                                        EventManager::start("sumo");
                                        EventManager::add($player);
                                        Server::getInstance()->broadcastMessage("§a» {$player->getName()} has started a sumo event, type /event join to join the event.");
                                    }
                                    break;
                                case "Legend":
                                    if($config->get("eventcooldown") >= time())
                                    {
                                        $player->sendMessage("§c» This command have a 10 minutes cooldown. (Higher ranks have less cooldown)");
                                    }else{
                                        $config->set("eventcooldown", time() + 900);
                                        $config->save();
                                        EventManager::start("sumo");
                                        EventManager::add($player);
                                        Server::getInstance()->broadcastMessage("§a» {$player->getName()} has started a sumo event, type /event join to join the event.");
                                    }
                                    break;
                                case "Booster":
                                    if($config->get("eventcooldown") >= time())
                                    {
                                        $player->sendMessage("§c» This command have a 1 day cooldown. (Higher ranks have less cooldown)");
                                    }else{
                                        $config->set("eventcooldown", time() + 86400);
                                        $config->save();
                                        EventManager::start("sumo");
                                        EventManager::add($player);
                                        Server::getInstance()->broadcastMessage("§a» {$player->getName()} has started a sumo event, type /event join to join the event.");
                                    }
                                    break;
                                case "Youtube":
                                    if($config->get("eventcooldown") >= time())
                                    {
                                        $player->sendMessage("§c» This command have a 1 day cooldown. (Higher ranks have less cooldown)");
                                    }else{
                                        $config->set("eventcooldown", time() + 86400);
                                        $config->save();
                                        EventManager::start("sumo");
                                        EventManager::add($player);
                                        Server::getInstance()->broadcastMessage("§a» {$player->getName()} has started a sumo event, type /event join to join the event.");
                                    }
                                    break;
                                case "Famous":
                                    if($config->get("eventcooldown") >= time())
                                    {
                                        $player->sendMessage("§c» This command have a 1 day cooldown. (Higher ranks have less cooldown)");
                                    }else{
                                        $config->set("eventcooldown", time() + 86400);
                                        $config->save();
                                        EventManager::start("sumo");
                                        EventManager::add($player);
                                        Server::getInstance()->broadcastMessage("§a» {$player->getName()} has started a sumo event, type /event join to join the event.");
                                    }
                                    break;
                                default:
                                    if($config->get("eventcooldown") >= time())
                                    {
                                        $player->sendMessage("§c» This command have a 10 minutes cooldown. (Higher ranks have less cooldown)");
                                    }else{
                                        $config->set("eventcooldown", time() + 900);
                                        $config->save();
                                        EventManager::start("sumo");
                                        EventManager::add($player);
                                        Server::getInstance()->broadcastMessage("§a» {$player->getName()} has started a sumo event, type /event join to join the event.");
                                    }
                                    break;
                            }
                            break;
                        default:
                            $player->sendMessage("§cPlease use /event join|start nodebuff|gapple|sumo.");
                            break;
                    }
                }
                break;
        }
    }
}