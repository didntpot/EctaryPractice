<?php

namespace practice\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\Server;
use practice\duels\DuelsProvider;
use practice\duels\form\DuelAdmin;
use practice\duels\form\DuelInvitation;
use practice\duels\manager\DuelsManager;
use practice\party\form\PartyForm;
use practice\party\PartyProvider;
use practice\api\PlayerDataAPI;

class Duel extends PluginCommand
{
    public function __construct($plugin)
    {
        parent::__construct("duel", $plugin);
        $this->setDescription("Duel command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if ($sender instanceof Player)
        {
            if (isset($args[0]))
            {
                switch ($args[0])
                {
                    case "admin":
                        if ($sender->hasPermission("duel.admin.command")) DuelAdmin::openAdminDuel($sender);
                        break;
                    case "decline":
                        if(DuelsManager::removeInvitation($sender))
                        {
                            $sender->sendMessage("§c» You've declined this duel invite.");
                        }else{
                            $sender->sendMessage("§c» You've declined this duel invite.");
                        }
                        break;
                    case "accept":
                        $last = DuelsManager::getLastInvitation($sender->getName());
                        if (is_null($last)) return $sender->sendMessage("§c» You don't have any invitation.");
                        DuelsManager::removeInvitation($last["player"]);
                        DuelsManager::createDuel([$sender->getName(), $last["player"]], $last["config_map"]);
                        break;
                    default:
                        $player = Server::getInstance()->getPlayer($args[0]);

                        if (!is_null($player))
                        {
                            if(PlayerDataAPI::getSetting($player->getName(), "duel_requests") === "false") return $sender->sendMessage("§c» This player does not accept duel requests.");
                            if ($player->getName() === $sender->getName()) return $sender->sendMessage("§c» You cannot invite your self.");
                            if (DuelsManager::isInDuel($player)) return $sender->sendMessage("§c» This player is in a duel.");
                            if (PartyProvider::hasParty($player->getName())) return $sender->sendMessage("§c» This player is in a party.");
                            DuelInvitation::openDuelKit($sender, $player->getName());
                        }else{
                            $sender->sendMessage("§c» This player is offline.");
                        }
                        break;
                }
            }
        }
    }
}