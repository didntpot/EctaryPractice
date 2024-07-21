<?php

namespace practice\duels\form;

use pocketmine\Player;
use practice\api\form\SimpleForm;
use practice\duels\DuelQueue;
use practice\duels\DuelsProvider;

class DuelsForm
{
    public static function openUnrankedForm(Player $player)
    {
        $form = new SimpleForm(function (Player $player, $data) {
            if ($data === null) return;
            if ((($data == 0) ? 0 : $data + 1) == count(DuelsProvider::LADDER_UNRANKED) + 1) return;
            if (!DuelQueue::isInQueue($player->getName()))
            {
                DuelQueue::searchOpponent($player, DuelsProvider::LADDER_UNRANKED[$data]);
            }else{
                $player->sendMessage("§c» You're already in a queue.");
            }
        });
        $form->setTitle("Unranked");
        $form->setContent("Select a ladder :");
        foreach (DuelsProvider::LADDER_UNRANKED as $id => $information)
        {
            $form->addButton($information["name"]."\n". DuelQueue::countQueue($information["name"], $information["type"])." in-queue", 0, $information["texture_button"]);
        }
        $form->addButton("« Exit");
        $form->sendToPlayer($player);
    }

    public static function openUnranked2Form(Player $player)
    {
        $form = new SimpleForm(function (Player $player, $data) {
            if ($data === null) return;
            if ((($data == 0) ? 0 : $data + 1) == count(DuelsProvider::LADDER_UNRANKED2) + 1) return;
            if (!DuelQueue::isInQueue($player->getName()))
            {
                DuelQueue::searchOpponent($player, DuelsProvider::LADDER_UNRANKED2[$data]);
            }else{
                $player->sendMessage("§c» You're already in a queue.");
            }
        });
        $form->setTitle("Unranked (2v2)");
        $form->setContent("Select a ladder :");
        foreach (DuelsProvider::LADDER_UNRANKED2 as $id => $information)
        {
            $form->addButton($information["name"]."\n". DuelQueue::countQueue($information["name"], $information["type"])." in-queue", 0, $information["texture_button"]);
        }
        $form->addButton("« Exit");
        $form->sendToPlayer($player);
    }

    public static function openRankedForm(Player $player)
    {
        $form = new SimpleForm(function (Player $player, $data) {
            if ($data === null) return;
            if ((($data == 0) ? 0 : $data + 1) == count(DuelsProvider::LADDER_RANKED) + 1) return;

            if (!DuelQueue::isInQueue($player->getName()))
            {
                DuelQueue::searchOpponent($player, DuelsProvider::LADDER_RANKED[$data]);
            }else{
                $player->sendMessage("§c» You're already in a queue.");
            }
        });
        $form->setTitle("Ranked");
        $form->setContent("Select a ladder :");
        foreach (DuelsProvider::LADDER_RANKED as $id => $information)
        {
            $form->addButton($information["name"]."\n". DuelQueue::countQueue($information["name"], $information["type"])." in-queue", 0, $information["texture_button"]);
        }
        $form->addButton("« Exit");
        $form->sendToPlayer($player);
    }
}