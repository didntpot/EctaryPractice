<?php

namespace practice\forms;

use practice\api\form\{
    SimpleForm,
    CustomForm};
use pocketmine\Player;
use pocketmine\Server;

class EventsForm
{
    public static function openForm(Player $player)
    {
        $form = new SimpleForm(function(Player $player, $data){
           if($data === null) return;

           switch($data)
           {
               case 0:
                   Server::getInstance()->dispatchCommand($player, "event join");
                   break;
               case 1:
                   self::openHostForm($player);
                   break;
           }
        });
        $form->setTitle("Events");
        $form->addButton("Join an event", 0, "textures/ui/dressing_room_skins");
        $form->addButton("Host an event", 0, "textures/ui/anvil_icon");
        $form->addButton("« Exit");
        $form->sendToPlayer($player);
    }

    public static function openHostForm(Player $player)
    {
        $form = new CustomForm(function(Player $player, $data){
           if($data === null) return;
           if(!$player->hasPermission("event.start.command")) return $player->sendMessage("§cYou do not have permission to use this.");


           switch($data[0])
           {
               case 0:
                   Server::getInstance()->dispatchCommand($player, "event start nodebuff");
                   break;
               case 1:
                   Server::getInstance()->dispatchCommand($player, "event start gapple");
                   break;
               case 2:
                   Server::getInstance()->dispatchCommand($player, "event start sumo");
                   break;
           }
        });
        $form->setTitle("Host an event");
        $form->addDropDown("Select a gamemode :", ["nodebuff", "gapple", "sumo"]);
        $form->sendToPlayer($player);
    }
}