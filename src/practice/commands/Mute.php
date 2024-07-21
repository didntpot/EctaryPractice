<?php


namespace practice\commands;


use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use practice\api\InformationAPI;
use practice\manager\BanManager;
use practice\manager\MuteManager;
use practice\manager\PlayerManager;
use practice\manager\TimeManager;
use practice\provider\WorkerProvider;
use pocketmine\scheduler\AsyncTask;
use practice\manager\SQLManager;
use practice\api\discord\{
    Webhook,
    Message,
    Embed
};

class Mute extends PluginCommand
{

    const T_MUTE = 0;
    const L_MUTE = 1;

    public function __construct(Plugin $owner)
    {
        parent::__construct("mute", $owner);
        $this->setDescription("Mute command");
        $this->setPermission("mute.command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$sender->hasPermission($this->getPermission())) return;

        /**
         * $args[0] $args[1] $args[2]
         * (name)   (time)  (reason)
         **/

        if (!isset($args[0]) or $args[0] === "help") return $sender->sendMessage("§a» Usage: \n- /mute (name) (time) (reason)\n- /mute (name) (reason)");

        $mute_player = Server::getInstance()->getPlayer($args[0]);
        $mute_name = (!is_null($mute_player)) ? $mute_player->getName() : $args[0];
        $region = InformationAPI::getServerRegion();
        $author = $sender->getName();

        if (isset($args[1]) and strtotime($args[1])) {

            $reason = "";
            for ($i = 2; $i < count($args); $i++) {
                $reason .= $args[$i];
                $reason .= " ";
            }

            $reason = (isset($args[2])) ? (BanManager::hasReason($reason)) ? BanManager::getReasonByNumber($reason) : $reason : "No reason";
            $mute_expire = strtotime($args[1]);
            $mute_type = self::T_MUTE;
        } else {
            $reason = "";
            for ($i = 1; $i < count($args); $i++) {
                $reason .= $args[$i];
                $reason .= " ";
            }
            $reason = (isset($args[1])) ? (BanManager::hasReason($reason)) ? BanManager::getReasonByNumber($reason) : $reason : "No reason";
            $mute_type = self::L_MUTE;
            $mute_expire = 0;
        }

        MuteManager::mutePlayer($mute_name, $author, $reason, $mute_type, $mute_expire, $region);
    }
}
