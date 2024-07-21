<?php

namespace practice\duels\form;

use practice\api\form\SimpleForm;
use practice\api\gui\InvMenu;
use practice\manager\PlayerManager;
use pocketmine\Player;
use pocketmine\Server;

class DuelEndForm
{
    public static function openForm(Player $player)
    {
        $form = new SimpleForm(function(Player $player, $data){
           if($data === null) return;

           switch($data)
           {
               case 0:
                   if(isset(PlayerManager::$opoInventory[$player->getName()]))
                   {
                       $menuName = "Their Inventory";
                       $menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);

                       if(isset(PlayerManager::$lastOpoName[$player->getName()]))
                       {
                           $menuName = PlayerManager::$lastOpoName[$player->getName()]."'s Inventory";
                       }

                       $menu->setName($menuName);
                       $inventory = $menu->getInventory();

                       foreach(PlayerManager::$opoInventory[$player->getName()][0] as $item)
                       {
                           $inventory->addItem($item);
                       }

                       $menu->send($player);
                   }else{
                       $player->sendMessage("§cNo inventory was found.");
                   }
                   break;
           }
        });
        $form->setTitle("Duel Overview");

        $health = "0";
        $yourHits = "0";
        $opoName = "";
        $theirHits = "";

        if(isset(PlayerManager::$lastOpoHealth[$player->getName()]))
        {
            $health = PlayerManager::$lastOpoHealth[$player->getName()];
        }

        if(isset(PlayerManager::$lastOpoName[$player->getName()]))
        {
            $opoName = PlayerManager::$lastOpoName[$player->getName()];

            $p = Server::getInstance()->getPlayer($opoName);

            if(!is_null($p))
            {
                $opoName = $p->getDisplayName();
            }

            if(isset(PlayerManager::$duelHits[$opoName]))
            {
                $theirHits = PlayerManager::$duelHits[$opoName];
            }
        }

        if(isset(PlayerManager::$duelHits[$player->getName()]))
        {
            $yourHits = PlayerManager::$duelHits[$player->getName()];
        }
        
        $form->addButton("View opponent inventory");
        $form->addButton("« Exit");

        $form->sendToPlayer($player);
    }
}