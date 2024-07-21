<?php

namespace practice\forms;

use practice\api\form\SimpleForm;
use pocketmine\Player;
use practice\manager\LeaderboardManager;

class LeaderboardsForm
{
    public static function openLbSelectionForm(Player $player)
    {
        $form = new SimpleForm(function (Player $player, $data) {
            if ($data === null) return;
            switch ($data) {
                case 0:
                    self::openKillsLbForm($player);
                    break;
                case 1:
                    self::openDeathsLbForm($player);
                    break;
                case 2:
                    self::openEloLbForm($player);
                    break;
                case 3:
                    self::openWinsLbForm($player);
                    break;
                case 4:
                    self::openLosesLbForm($player);
                    break;
                case 5:
                    self::openKillStreakLbForm($player);
                    break;
            }
        });
        $form->setTitle("Leaderboards");
        $form->addButton("Kills");
        $form->addButton("Deaths");
        $form->addButton("Elo");
        $form->addButton("Wins");
        $form->addButton("Losses");
        $form->addButton("Kill-streak");
        $form->addButton("« Exit");
        $form->sendToPlayer($player);
    }

    public static function openEloLbForm(Player $player)
    {
        $form = new SimpleForm(function (Player $player, $data) {
            if ($data === null) return;
            switch ($data) {
                case 50:
                    self::openLbSelectionForm($player);
                    break;
            }
        });
        $form->setTitle("Elo Leaderboard");
        $content = LeaderboardManager::getCacheLeaderboard()["elo"];
        foreach ($content as $id => $item) {
            $id++;
            $form->addButton("$id. ". $item["name"] . "\n" . $item["elo"]);
        }
        $form->addButton("« Back");
        $form->sendToPlayer($player);
    }

    public static function openWinsLbForm(Player $player)
    {
        $form = new SimpleForm(function (Player $player, $data) {
            if ($data === null) return;
            switch ($data) {
                case 50:
                    self::openLbSelectionForm($player);
                    break;
            }
        });
        $form->setTitle("Wins Leaderboard");
        $content = LeaderboardManager::getCacheLeaderboard()["wins"];
        foreach ($content as $id => $item) {
            $id++;
            $form->addButton("$id. ".$item["name"] . "\n" . $item["wins"]);
        }
        $form->addButton("« Back");
        $form->sendToPlayer($player);
    }

    public static function openLosesLbForm(Player $player)
    {
        $form = new SimpleForm(function (Player $player, $data) {
            if ($data === null) return;
            switch ($data) {
                case 50:
                    self::openLbSelectionForm($player);
                    break;
            }
        });
        $form->setTitle("Loses Leaderboard");
        $content = LeaderboardManager::getCacheLeaderboard()["loses"];
        foreach ($content as $id => $item) {
            $id++;
            $form->addButton("$id. ".$item["name"] . "\n" . $item["loses"]);
        }
        $form->addButton("« Back");
        $form->sendToPlayer($player);
    }

    public static function openKillsLbForm(Player $player)
    {
        $form = new SimpleForm(function (Player $player, $data) {
            if ($data === null) return;
            switch ($data) {
                case 50:
                    self::openLbSelectionForm($player);
                    break;
            }
        });
        $form->setTitle("Kills Leaderboard");
        $content = LeaderboardManager::getCacheLeaderboard()["kills"];
        foreach ($content as $id => $item) {
            $id++;
            $form->addButton("$id. ".$item["name"] . "\n" . $item["kill"]);
        }
        $form->addButton("« Back");
        $form->sendToPlayer($player);
    }

    public static function openDeathsLbForm(Player $player)
    {
        $form = new SimpleForm(function (Player $player, $data) {
            if ($data === null) return;
            switch ($data) {
                case 50:
                    self::openLbSelectionForm($player);
                    break;
            }
        });
        $form->setTitle("Deaths Leaderboard");
        $content = LeaderboardManager::getCacheLeaderboard()["death"];
        foreach ($content as $id => $item) {
            $id++;
            $form->addButton("$id. ".$item["name"] . "\n" . $item["death"]);
        }
        $form->addButton("« Back");
        $form->sendToPlayer($player);
    }

    public static function openKillStreakLbForm(Player $player)
    {
        $form = new SimpleForm(function (Player $player, $data) {
            if ($data === null) return;
            switch ($data) {
                case 50:
                    self::openLbSelectionForm($player);
                    break;
            }
        });
        $form->setTitle("Kill-Streak Leaderboard");
        $content = LeaderboardManager::getCacheLeaderboard()["kill_streaks"];
        foreach ($content as $id => $item) {
            $id++;
            $form->addButton("$id. ".$item["name"] . "\n" . $item["kill_streak"]);
        }
        $form->addButton("« Back");
        $form->sendToPlayer($player);
    }
}