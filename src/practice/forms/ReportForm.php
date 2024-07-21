<?php

namespace practice\forms;

use practice\api\InformationAPI;
use practice\api\form\{
    SimpleForm,
    CustomForm
};
use practice\api\discord\{
    Webhook,
    Message,
    Embed
};
use practice\forms\PlayerPerksForm;
use pocketmine\Player;
use pocketmine\Server;

class ReportForm
{
    public static array $playerList = [];

    public static function openCommandSelectionForm(Player $player)
    {
        $form = new SimpleForm(function (Player $player, $data) {
            if ($data === null) return;
            switch ($data) {
                case 0:
                    self::openPlayerReportForm($player);
                    break;
                case 1:
                    self::openIssueReportForm($player);
                    break;
            }
        });
        $form->setTitle("Report");
        $form->addButton("Report a player");
        $form->addButton("Report an issue");
        $form->addButton("« Exit");
        $form->sendToPlayer($player);
    }

    public static function openMenuSelectionForm(Player $player)
    {
        $form = new SimpleForm(function (Player $player, $data) {
            if ($data === null) return;
            switch ($data) {
                case 0:
                    self::openPlayerReportForm($player);
                    break;
                case 1:
                    self::openIssueReportForm($player);
                    break;
                case 2:
                    PlayerPerksForm::openForm($player);
                    break;
            }
        });
        $form->setTitle("Report");
        $form->addButton("Report a player");
        $form->addButton("Report an issue");
        $form->addButton("« Back");
        $form->sendToPlayer($player);
    }

    public static function openPlayerReportForm(Player $player)
    {
        $list = [];
        foreach (Server::getInstance()->getOnlinePlayers() as $p) {
            $list[] = $p->getName();
        }

        self::$playerList[$player->getName()] = $list;

        $form = new CustomForm(function (Player $player, ?array $data) {

            if($data === null) return;

            if ($data[1] === null && $data[0] === null) {
                return true;
            } else {
                $index = $data[0];

                $playerName = self::$playerList[$player->getName()][$index];

                $webHook = new Webhook("https://discord.com/api/webhooks/817260229124423691/Zskr2ukh-bmbtELt0a2698seopyPg9Phm-kQXtoVu0SM3rT-b-X1lFXR24csfEK6Imw0");

                $msg = new Message();
                $msg->setUsername("Ectary Network");

                $embed = new Embed();
                $embed->setTitle("Player Report");
                $embed->setDescription("**Reported Player:** $playerName\n**Reason:** $data[1]\n**Region:** ". InformationAPI::getServerReportRegion());
                $embed->setFooter("Sender: " . $player->getName());
                $msg->addEmbed($embed);

                $webHook->send($msg);

                foreach (Server::getInstance()->getOnlinePlayers() as $players) {
                    if ($players->hasPermission("staff.command")) {
                        $players->sendMessage("§c» $playerName has been reported for $data[1].");
                    }
                }

                $player->sendMessage("§a» Thank you for reporting this player!\n§cBe aware, useless report and troll reports are forbidden and may result a ban.");
            }
        });
        $form->setTitle("Report a player");
        $form->addDropdown("Select the player that you would like to report :", self::$playerList[$player->getName()]);
        $form->addInput("Why are you reporting this player, give us the exact information :", "...");
        $form->sendToPlayer($player);
    }

    public static function openIssueReportForm(Player $player)
    {
        $form = new CustomForm(function (Player $player, $data) {
            if($data === null) return;
            if ($data[0] === null) {
            } else {

                $webHook = new Webhook("https://discord.com/api/webhooks/817260229124423691/Zskr2ukh-bmbtELt0a2698seopyPg9Phm-kQXtoVu0SM3rT-b-X1lFXR24csfEK6Imw0");

                $msg = new Message();
                $msg->setUsername("Ectary Network");

                $embed = new Embed();
                $embed->setTitle("Issue Report");
                $embed->setDescription("**Issue Details:** $data[0]\n**Region:** ". InformationAPI::getServerReportRegion());
                $embed->setFooter("Sender: {$player->getName()}");
                $msg->addEmbed($embed);

                $webHook->send($msg);

                foreach (Server::getInstance()->getOnlinePlayers() as $players) {
                    if ($players->hasPermission("staff.command")) {
                        $players->sendMessage("§c» {$player->getName()} has reported the following issue(s): $data[0].");
                    }
                }
            }
        });
        $form->setTitle("Report an issue");
        $form->addInput("Give us all the details you can :", "...");
        $form->sendToPlayer($player);
    }
}