<?php


namespace practice\forms;


use pocketmine\Player;
use practice\api\form\CustomForm;
use practice\api\form\SimpleForm;
use practice\api\PlayerDataAPI;
use practice\commands\Tags;
use practice\manager\PlayerManager;
use practice\manager\TagsManager;
use practice\manager\TimeManager;

class TagsForm
{
    const MAX_CUSTOM_TAGS = 20;
    const PERMISSION = "custom.tags";

    const TIME_COOLDOWN = 5;
    public static array $cooldown = [];


    public static function openTagsForm(Player $player)
    {
        if (!isset(self::$cooldown[$player->getLowerCaseName()]) or self::$cooldown[$player->getLowerCaseName()] <= time()) {
            $form = new SimpleForm(function (Player $player, $data) {
                if (is_null($data)) return;
                if (is_null(TagsForm::getButtons($player))) return self::openTagsForm($player);
                $action = TagsForm::getButtons($player)[$data];
                if ($action["tags_name"] === "quit") return PlayerPerksForm::openCosmeticsForm($player);
                if ($action["tags_name"] === PlayerManager::getInformation($player->getName(), "tags")) return $player->sendMessage("§a» Your tags has been updated.");
                if ($action["tags_name"] === "custom_tags") return TagsForm::openCustomTags($player);
                if ($action["tags_name"] === "remove") {
                    $player->sendMessage("§a» Your tag has been successfully deleted.");
                    return PlayerManager::setInformation($player->getName(), "tags", "");
                }
                if ($player->hasPermission($action["permission"])) {
                    PlayerManager::setInformation($player->getName(), "tags", $action["tags_name"]);
                    $player->sendMessage("§a» Your tags has been updated.");
                    self::$cooldown[$player->getLowerCaseName()] = time() + self::TIME_COOLDOWN;
                } else {
                    $player->sendMessage("§c» You do not have permission to use this.");
                }
            });
            $form->setTitle("Tags");
            foreach (TagsForm::getButtons($player) as $button) {
                $form->addButton($button["text"]);
            }
            $form->sendToPlayer($player);
        } else {
            $player->sendMessage("§c» Tag menu is on cooldown for " . TimeManager::timestampToTime(self::$cooldown[$player->getLowerCaseName()])["second"] . " second(s).");
        }
    }

    public static function openCustomTags(Player $player)
    {
        $form = new CustomForm(function (Player $player, $data) {
            if (empty($data)) return;
            if (!$player->hasPermission(self::PERMISSION)) return $player->sendMessage("§a» You do not have permission to use this.");
            $custom = $data[0];
            if ($custom === PlayerManager::getInformation($player->getName(), "tags")) return $player->sendMessage("§a» Your custom tag has been updated.");
            if (strlen(str_replace("§", "", $custom)) <= self::MAX_CUSTOM_TAGS) {
                $player->sendMessage("§a» Your custom tag has been updated.");
                PlayerManager::setInformation($player->getName(), "tags", $custom);
            } else {
                $player->sendMessage("§c» Your custom tag is containing too many characters (Max: 20).");
            }
        });
        $form->setTitle("Tags");
        $form->addInput("Enter a custom tag :", "...");
        $form->sendToPlayer($player);
    }

    private static function getButtons(Player $player)
    {
        $buttons = [];
        $buttons[] = ["text" => "Custom Tags", "tags_name" => "custom_tags"];
        if (is_null(TagsManager::getAllTags())) return null;
        foreach (TagsManager::getAllTags() as $tag) {
            if ($player->hasPermission($tag["permission"])) {
                $buttons[] = ["text" => $tag["tags_name"] . "\n§aUnlocked", "tags_name" => $tag["tags_name"], "permission" => $tag["permission"]];
            } else {
                $buttons[] = ["text" => $tag["tags_name"] . "\n§cLocked", "tags_name" => $tag["tags_name"], "permission" => $tag["permission"]];
            }
        }
        $buttons[] = ["text" => "Remove your tag", "tags_name" => "remove"];
        $buttons[] = ["text" => "« Back", "tags_name" => "quit"];
        return $buttons;
    }
}