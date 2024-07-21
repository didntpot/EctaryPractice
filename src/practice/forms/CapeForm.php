<?php


namespace practice\forms;

use pocketmine\event\Listener;
use pocketmine\Player;
use practice\api\form\SimpleForm;
use practice\api\InformationAPI;
use practice\manager\CapesManager;
use practice\manager\PlayerManager;

class CapeForm implements Listener
{
    public static function openCapesForm(Player $player)
    {
        $capes = CapesManager::getAllCape();
        if (is_null(CapesManager::getAllCape())) return PlayerPerksForm::openForm($player);
        $form = new SimpleForm(function (Player $player, $data) {
            if (is_null($data)) return;

            if ($data == (count(CapesManager::getAllCapeName()))) {
                PlayerManager::removeCape($player);
                PlayerManager::setInformation($player->getName(), "cape_select", "");
                return $player->sendMessage("§a» Your cape has been successfully removed.");
            }
            if ($data == (count(CapesManager::getAllCapeName()) + 1)) return PlayerPerksForm::openCosmeticsForm($player);
            $cape = CapesManager::getAllCape()[$data];
            if (!$player->hasPermission($cape["permission"])) return $player->sendMessage("§c» You do not have permission to use this.");
            if (PlayerManager::getInformation($player->getName(), "cape_select") === $cape["cape_name"]) return;
            PlayerManager::setCape($player, $cape["cape_name"]);
        });
        $form->setTitle("Capes");
        foreach ($capes as $cape) {
            ($player->hasPermission($cape["permission"])) ? $form->addButton($cape["cape_name"] . "\n§aUnlocked", 1, str_replace("{ip}", InformationAPI::$ip, $cape["cape_image_link"])) : $form->addButton($cape["cape_name"] . "\n§cLocked", 1, str_replace("{ip}", InformationAPI::$ip, $cape["cape_image_link"]));
        }
        $form->addButton("Remove your cape");
        $form->addButton("« Back");
        $form->sendToPlayer($player);
    }
}