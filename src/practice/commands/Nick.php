<?php

namespace practice\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use practice\manager\PlayerManager;

class Nick extends PluginCommand
{
    public function __construct($plugin)
    {
        parent::__construct("nick", $plugin);
        $this->setDescription("Nick command");
        $this->setPermission("nick.command");
    }

    const NICKS = ["SpongeBobYT", "xWitheredApolo", "DeexieMC", "Gintakini2929", "GornicBlayDay", "PhantomRested360", "OnieuRxic", "LucaSundered99293", "KKeeekeMC", "Luparis Tungered", "zTrmonico", "ShrimpsonTTV", "OffRoadWatchedII", "CurkLoadLOL", "EarnIe24", "MLGMouseStick", "MelonNight237576", "DayBreededBee", "CupcakeFarmed993", "xRqcarn", "Cnadaie383", "Imeteiieo90", "HardooniSundai", "AlleinAwareness", "zRversey", "SautieLePaulo11", "RoyPlayzMC", "ReoLiphoher3993", "Dillipicokoloc", "IvoryWinter9988", "Nicomanded29290", "YuaruoisMCPE", "NickPotsEasily", "GamerBoy1220", "EpicGamerrrrXD", "6Blockinq", "AstelisionDov", "F4voredpotting", "Masalak22556", "JaydenAintANoob", "Onirusick99", "NotChoosen9838", "161BR", "BrickGang567686"];

    public function execute(CommandSender $player, string $commandLabel, array $args)
    {
        if ($player instanceof Player) {
            if (!$player->hasPermission("nick.command")) return $player->sendMessage("§cYou do not have permission to use this command");

            if (isset($args[0])) {
                if($args[0] === "remove" or $args[0] === "off" and isset(PlayerManager::$nickname[$player->getName()]))
                {
                    unset(PlayerManager::$nickname[$player->getName()]);
                    $player->sendMessage("§a» Your name is now displayed as ". $player->getName().".");
                    return $player->setDisplayName($player->getName());
                }

            }
            if(isset($args[0]) && $args[0] === "custom")
            {
                if($player->hasPermission("nick.custom"))
                {
                    if(isset($args[1]))
                    {
                        unset(PlayerManager::$nickname[$player->getName()]);
                        PlayerManager::$nickname[$player->getName()] = true;
                        $player->sendMessage("§a» Your name is now displayed as $args[1].");
                        $player->setDisplayName($args[1]);
                        $player->setNameTag("§c{$player->getDisplayName()}");
                    }else{
                        $player->sendMessage("§cUse /nick custom <nick>");
                    }
                }else{
                    $player->sendMessage("§cYou do not have permission to use custom nicks.");
                }
            }else{
                PlayerManager::$nickname[$player->getName()] = true;
                $random = self::NICKS[array_rand(self::NICKS)];
                $player->setDisplayName($random);
                $player->sendMessage("§a» Your name is now displayed as $random.");

                $player->setNameTag("§c{$player->getDisplayName()}");
            }
        }
    }
}