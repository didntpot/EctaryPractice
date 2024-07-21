<?php


namespace practice\duels\form;


use pocketmine\Player;
use pocketmine\Server;
use practice\api\form\SimpleForm;
use practice\duels\DuelQueue;
use practice\duels\DuelsProvider;
use practice\duels\manager\DuelsManager;

class DuelInvitation
{
    public static function openDuelKit(Player $player, $opponent)
    {
        $form = new SimpleForm(function (Player $player, $data) use ($opponent){
            if (is_null($data)) return;
            if ((($data == 0) ? 0 : $data + 1) == count(DuelsProvider::LADDER_UNRANKED) + 1) return self::openDuelKit($player, $opponent);
            $opponent = Server::getInstance()->getPlayer($opponent);
            if (!is_null($opponent))
            {
                $map = DuelsProvider::LADDER_UNRANKED[$data];
               if (DuelsManager::addInvitation($opponent->getName(), $player->getName(), $map)){
                   $player->sendMessage("§a» You've sent a duel invite to ". $opponent->getName().".");
                   $opponent->sendMessage("§a» ".$player->getDisplayName() ." has sent you a duel invitation with type ".$map["name"].", type /duel accept to accept it.");
               }else{
                   $player->sendMessage("§c» You've already invited this player");
               }

            }else{
                $player->sendMessage("§c» This player is offline.");
            }
        });
        $form->setTitle("Select a mode");
        foreach (DuelsProvider::LADDER_UNRANKED as $id => $information)
        {
            $form->addButton($information["name"], 0, $information["texture_button"]);
        }
        $form->addButton("« Exit");
        $form->sendToPlayer($player);
    }
}