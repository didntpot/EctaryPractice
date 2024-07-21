<?php


namespace practice\manager;


use pocketmine\Server;
use practice\api\PlayerDataAPI;

class ChatManager
{
    public static array $after_chat = [];
    public static string $DEFAULT = "{tags} §7[{division}] §a{name}§7: §f{message}";
    public static int $max_maj = 20;

    public static function getSyntax($player_name, $text, $fake_name = null): string
    {
        $player = Server::getInstance()->getPlayer($player_name);

        $group = PlayerManager::getInformation($player->getName(), "group");
        $syntax = ChatManager::getGroupSyntax($group);
        $syntax = (!is_null($fake_name)) ? str_replace("{name}", $fake_name, ChatManager::getGroupSyntax("Basic")) : str_replace("{name}", $player_name, $syntax);

        if (!is_null(Server::getInstance()->getPlayer($player_name))) {
            if (!$player->hasPermission("color.chat")) $text = str_replace("§", "", $text);
        }
        $tags = PlayerManager::getInformation($player_name, "tags");

        $division = PlayerManager::getInformation($player_name, "division");

        $syntax = str_replace("{message}", $text, $syntax);
        $syntax = (!empty($division) and !is_null($division)) ? str_replace("{division}", $division, $syntax) : trim(str_replace("{division}", "", $syntax));
        $syntax = (!empty($tags) and !is_null($tags)) ? str_replace("{tags}", $tags, $syntax) : trim(str_replace("{tags}", "", $syntax));
        return $syntax;
    }

    public static function setAfterChat($name, $text)
    {
        ChatManager::$after_chat[$name] = $text;
    }

    public static function hasSecurity($name, $text): bool
    {
        $http = strpos($text, 'http://');
        $https = strpos($text, 'https://');

        if ($http !== false or $https !== false) {
            Server::getInstance()->getLogger()->info("[Practice] $name send link (" .  mb_strimwidth($text, 0, 10, "...") .")");
            return false;
        }

        if (!isset(ChatManager::$after_chat[$name])) return true;
        if (ChatManager::$after_chat[$name] === (string)$text) {
            Server::getInstance()->getLogger()->info("[Practice] $name spam this: " . mb_strimwidth($text, 0, 10, "..."));
            return false;
        }
        return true;
    }

    public static function getGroupSyntax($group_name)
    {
        if (!isset(GroupManager::$group_cache[$group_name])) return ChatManager::$DEFAULT;
        $syntax = GroupManager::$group_cache[$group_name]["syntax"];
        if (is_null($syntax) or empty($syntax)) return ChatManager::$DEFAULT;
        return $syntax;
    }
}