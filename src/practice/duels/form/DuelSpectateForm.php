<?php


namespace practice\duels\form;


use pocketmine\Player;
use practice\api\form\SimpleForm;
use practice\duels\Duels;
use practice\duels\DuelsProvider;
use practice\duels\manager\DuelsManager;
use practice\events\listener\PlayerJoin;
use practice\scoreboard\{DuelSpectateScoreboard};

class DuelSpectateForm
{

    public static function openSpectateForm(Player $player)
    {
        DuelsProvider::$duel_spectate[$player->getName()] = DuelsManager::getAllDuelById();

        $form = new SimpleForm(function (Player $player, $data){
            if (is_null($data) or empty(DuelsProvider::$duel_spectate[$player->getName()])) return;
            if ((($data == 0) ? 0 : $data + 1) == count(DuelsProvider::$duel_spectate[$player->getName()]) + 1) return;

            $duel = DuelsProvider::$duel_spectate[$player->getName()][$data];

            if ($duel instanceof Duels)
            {
                if (in_array($duel->getStatus(), [0, 1, 2])) return $player->sendMessage("§c» This match is not started yet.");
                if ($duel->getStatus() == 4) return $player->sendMessage("§c» This match has already ended.");
                if($duel->getKit() == "boxing") return $player->sendMessage("§c» Boxing does not support spectators yet.");
                if($duel->getKit() == "thebridge") return $player->sendMessage("§c» The Bridge does not support spectators yet.");
                if($duel->getKit() == "hikabrain") return $player->sendMessage("§c» Hikabrain does not support spectators yet.");
                $duel->addSpectator($player->getName());
                unset(PlayerJoin::$scoreboard[$player->getName()]);
                PlayerJoin::$scoreboard[$player->getName()] = new DuelSpectateScoreboard($player);
                PlayerJoin::$scoreboard[$player->getName()]->sendRemoveObjectivePacket();
                DuelSpectateScoreboard::createLines($player);
            }
        });
        $form->setTitle("Spectate");
        if (empty(DuelsManager::getAllDuel()))
        {
        }else{
            foreach (DuelsProvider::$duel_spectate[$player->getName()] as $id => $item)
            {
                if ($item instanceof Duels)
                {
                    $players = implode(" vs ", $item->getPlayers());
                    $form->addButton("$players\n". $item->getType()." | ". $item->getKit());
                }
            }
        }
        $form->addButton("« Exit");
        $form->sendToPlayer($player);
    }
}