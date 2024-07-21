<?php

namespace practice\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Server;
use practice\party\PartyProvider;

class Party extends PluginCommand
{

    public function __construct($plugin)
    {
        parent::__construct("party", $plugin);
        $this->setDescription("Party command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        return $sender->sendMessage("§c» Use party UI please.");
        if (!isset($args[0])) return $sender->sendMessage("§c» Command usage: /party (create, start, stop, accept, decline, kick, invite, member, public)");

        switch ($args[0]) {
            case "create":
                if (!isset($args[1])) return $sender->sendMessage("§c» Command usage: /party create (" . implode(", ", array_column(PartyProvider::getWorld(), "name")) . ")");
                if (!in_array($args[1], array_column(PartyProvider::getWorld(), "name"))) return $sender->sendMessage("§c» The game mode was not found.");
                if (PartyProvider::hasParty($sender->getName())) return $sender->sendMessage("§c» You already have a party.");
                PartyProvider::createParty($sender, $args[1]);
                break;
            case "start":
                if (!PartyProvider::hasParty($sender->getName())) return $sender->sendMessage("§c» You don't have a party.");
                $party = PartyProvider::getParty($sender->getName());
                if ($sender->getName() !== $party->getLeader()) return $sender->sendMessage("§c» You don't have permission");
                if (!is_null($party)) $party->start();
                break;
            case "stop":
                if (!PartyProvider::hasParty($sender->getName())) return $sender->sendMessage("§c» You don't have a party to delete.");
                $party = PartyProvider::getParty($sender->getName());
                if ($sender->getName() !== $party->getLeader()) return $sender->sendMessage("§c» You don't have permission");
                if (!is_null($party)) $party->stop();
                break;
            case "accept":
                if (PartyProvider::hasParty($sender->getName())) return $sender->sendMessage("§c» You are already at a party.");
                if (empty(PartyProvider::getInvite($sender->getName()))) return $sender->sendMessage("§c» You have no invitation for a party.");
                if (!isset($args[1])) return $sender->sendMessage("§c» Command usage: /party accept (" . implode(", ", array_column(PartyProvider::getInvite($sender->getName()), "leader_name")) . ")");
                if (!in_array($args[1], array_column(PartyProvider::getInvite($sender->getName()), "leader_name"))) return $sender->sendMessage("§c» Invitation not found");
                $party = PartyProvider::getParty($args[1]);
                if (!is_null($party)) {
                    $player = Server::getInstance()->getPlayer($args[1]);
                    $party->addMember($sender->getName());
                    $sender->sendMessage("§a» You have accepted the invitation from " . $player->getName() . ".");
                    $party->sendMessageToLeader("§a» " . $sender->getName() . " accepted the invitation.");
                }
                break;
            case "decline":
                if (empty(PartyProvider::getInvite($sender->getName()))) return $sender->sendMessage("§c» You have no invitation for a party.");
                if (!isset($args[1])) return $sender->sendMessage("§c» Command usage: /party decline (" . implode(", ", array_column(PartyProvider::getInvite($sender->getName()), "leader_name")) . ")");
                if (!in_array($args[1], array_column(PartyProvider::getInvite($sender->getName()), "leader_name"))) return $sender->sendMessage("§c» Invitation not found");
                $party = PartyProvider::getParty($args[1]);
                if (!is_null($party)) {
                    $party->removeAllInvite($sender->getName());
                    $sender->sendMessage("§a» You have declined the invitation from " . $party->getLeader() . ".");
                    $party->sendMessageToLeader("§a» " . $sender->getName() . " declined your invitation");
                }
            case "kick":
                if (!PartyProvider::hasParty($sender->getName())) return $sender->sendMessage("§c» You don't have a party.");
                if (!isset($args[1])) return $sender->sendMessage("§c» Command usage: /party kick (name)");
                $party = PartyProvider::getParty($sender->getName());
                if ($party->getLeader() === $sender->getName()) return $sender->sendMessage("§c» You can't kick yourself.");
                if (!is_null($party)) {
                    $player = Server::getInstance()->getPlayer($args[1]);
                    if ($sender->getName() !== $party->getLeader()) return $sender->sendMessage("§c» You don't have permission");
                    $party->removeMember($player->getName());
                    $sender->sendMessage("§a» " . $player->getName() . " was the kicked of your party.");
                    $player->sendMessage("§a» You got kicked out of the party.");
                }
                break;
            case "invite":
                if (!PartyProvider::hasParty($sender->getName())) return $sender->sendMessage("§c» You don't have a party.");
                if (!isset($args[1])) return $sender->sendMessage("§c» Command usage: /party invite (name)");
                if ($args[1] === $sender->getName()) return $sender->sendMessage("§c» You cannot send yourself an invitation.");
                if (is_null(Server::getInstance()->getPlayer($args[1]))) return $sender->sendMessage("§c» The player is not online.");
                $party = PartyProvider::getParty($sender->getName());
                if ($sender->getName() !== $party->getLeader()) return $sender->sendMessage("§c» You don't have permission");
                if (!is_null($party)) {
                    $player = Server::getInstance()->getPlayer($args[1]);
                    $party->addInvite($args[1]);
                    $sender->sendMessage("§a» An invitation has been sent to " . $player->getName());
                    $player->sendMessage("§a» " . $sender->getName() . " just invited you to that party.");
                }
                break;
            case "member":
                if (!PartyProvider::hasParty($sender->getName())) return $sender->sendMessage("§c» You don't have a party.");
                break;
            case "public":
                if (empty(PartyProvider::getPublicPartys()[0])) return $sender->sendMessage("§c» No public party has started.");
                $message = "§a» List of public party:§e";
                foreach (PartyProvider::getPublicPartys()[0] as $party) {
                    $message .= "\n- " . $party->getLeader() . " - ". $party->getMemberCount() ." (" . $party->getMode() . ")";
                }
                $sender->sendMessage($message);
                break;
        }
    }
}