<?php


namespace practice\events\listener;


use pocketmine\event\Listener;
use practice\manager\ChatManager;
use practice\manager\PlayerManager;
use pocketmine\Server;

class PlayerChatEvent implements Listener
{

    public function onChat(\pocketmine\event\player\PlayerChatEvent $event)
    {
        $player = $event->getPlayer();

        if(isset(PlayerManager::$staff_chat[$player->getName()]) && PlayerManager::$staff_chat[$player->getName()] === true)
        {
            self::sendMessageTo(Server::getInstance()->getOnlinePlayers(), "§b[§7STAFF CHAT§b] §7{$player->getName()} : {$event->getMessage()}");
            $event->setCancelled();
        }else{
            $name = (isset(PlayerManager::$nickname[$player->getName()])) ? $player->getDisplayName() : null;

            if (ChatManager::hasSecurity($player->getName(), $event->getMessage())) {
                $event->setFormat(ChatManager::getSyntax($player->getName(), $event->getMessage(), $name));
            } else {
              if($player->isOp()){
                $event->setFormat(ChatManager::getSyntax($player->getName(), $event->getMessage(), $name));
              }else{
                  $player->sendMessage("§c» Your message has been blocked.");
                  $event->setCancelled();
              }
            }

            ChatManager::setAfterChat($player->getName(), $event->getMessage());
        }
    }

    public static function sendMessageTo(array $players, $message)
    {
        foreach($players as $staff)
        {
            if($staff->hasPermission("staff.command")) $staff->sendMessage($message);
        }
    }
}