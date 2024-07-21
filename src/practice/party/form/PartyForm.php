<?php

namespace practice\party\form;

use pocketmine\Player;
use pocketmine\Server;
use practice\api\form\CustomForm;
use practice\api\form\ModalForm;
use practice\api\form\SimpleForm;
use practice\forms\TagsForm;
use practice\manager\PlayerManager;
use practice\manager\TimeManager;
use practice\party\PartyProvider;
use practice\party\PartySystem;
use practice\forms\PlayerPerksForm;

class PartyForm
{
    # NOTE: Etape 1
    public static function openDefaultPartyUI(Player $player)
    {
        if ($player instanceof Player) {
            if (!PartyProvider::hasParty($player->getName())) {
                PartyForm::openDefaultUI($player);
            } else {
                $party = PartyProvider::getParty($player->getName());
                if (!is_null($party)) {
                    if ($party->isLeader($player->getName())) {
                        PartyForm::openLeadertUI($player);
                    } else {
                        PartyForm::openMembertUI($player);
                    }
                }
            }
        }
    }

    # NOTE: Etape 2 si tu crée
    private static function openDefaultUI(Player $player)
    {
        $form = new SimpleForm(function (Player $player, $data) {
            if ($data === null) return;
            switch ($data) {
                case 0:
                    PartyForm::openModeUI($player);
                    break;
                case 1:
                    PartyForm::openManageInvitetUI($player);
                    break;
                case 2:
                    PartyForm::openPublicParty($player);
                    break;
            }
        });
        $number_public = (is_null(PartyProvider::getPublicPartys())) ? 0 : PartyProvider::getPublicPartys()[1];
        $form->setTitle("Party");
        $form->addButton("Create");
        $form->addButton("Invites [" . PartyProvider::getInviteCount($player->getName()) . "]");
        $form->addButton("Public Parties [". $number_public."]");
        $form->addButton("« Exit");
        $form->sendToPlayer($player);
    }

    public static function openPublicParty($player)
    {
        if (is_null(PartyProvider::getPublicPartys())) return;

        $form = new SimpleForm(function (Player $player, $data) {
            if (is_null($data)) return;
            if (isset(PartyProvider::getPublicPartys(true)[0][$data])) {
                $party = PartyProvider::getPublicPartys(true)[0][$data];
                if ($party instanceof PartySystem)
                {
                    if ($party->getType() === "public") {
                        if ($party->getMemberCount() >= $party->getMaxSlot()) return $player->sendMessage("§c» This party is full.");
                        if ($party->addMember($player->getName()))
                        {
                            $party->sendMessageToLeader("§a» " . $player->getName() . " joined the party.");
                        }
                    } else {
                        $player->sendMessage("§c» This party is not public.");
                    }
                }
            }
        });
        $form->setTitle("Party");
        foreach (PartyProvider::getPublicPartys()[0] as $name => $party) {
            if ($party instanceof PartySystem)
                $form->addButton($party->getLeader() . "\n" . $party->getMode()." - ". $party->getMemberCount() ." - ". TimeManager::timestampDiffToTime($party->start_time, time()));
        }
        $form->sendToPlayer($player);
    }

    # NOTE: Etape 3 pour choisir le mode de jeux
    private static function openModeUI(Player $player)
    {
        $form = new SimpleForm(function (Player $player, $data) {
            $worlds = PartyProvider::getWorld();
            if (is_null($data)) return;
            if (empty($worlds)) return $player->sendMessage("§c» The party system is under maintenance (No world found)");
            if ((($data == 0) ? 0 : $data + 1) == count($worlds) + 1) return;
            $mode = PartyProvider::getWorld()[$data]["type"];
            $world_name = PartyProvider::getWorld()[$data]["name"];
            if (PartyProvider::hasParty($player->getName())) {
                $party = PartyProvider::getParty($player->getName());
                $party->updateMode($mode, $world_name);
            } else {
                $party = PartyProvider::createParty($player, $mode);
                if (!is_null($party)) $party->setType("private");
            }
        });
        $worlds = PartyProvider::getWorld();
        if (empty($worlds)) return $player->sendMessage("§c» The party system is under maintenance (No world found)");
        $form->setTitle("Party");
        foreach ($worlds as $world => $info) {
            if (PartyProvider::hasParty($player->getName()))
            {
                $party = PartyProvider::getParty($player->getName());
                $name = (!is_null($party->getWorldName()) and $info["name"] === $party->getWorldName()) ? "» ". $info["name"] ." «" : $info["name"];
                $form->addButton($name);
            }else{
                $form->addButton($info["name"]);
            }
        }
        $form->sendToPlayer($player);
    }

    private static function openLeadertUI(Player $player)
    {
        $form = new SimpleForm(function (Player $player, $data) {
            if ($data === null) return;
            $party = PartyProvider::getParty($player->getName());
            if (is_null($party)) return PartyForm::openDefaultPartyUI($player);

            switch ($data) {
                case 0:
                    PartyForm::openInvitetUI($player);
                    break;
                case 1:
                    PartyForm::openSettingsUI($player);
                    break;
                case 2:
                    $party = PartyProvider::getParty($player->getName());
                    if ($party->getWorldGenerate() !== true) {
                        if (!is_null($party) and $party->getMemberCount() >= PartyProvider::$min_player) $party->start();
                    } else {
                        if (!is_null($party) and $party->getMemberCount() >= PartyProvider::$min_player) $party->teleportPlayer($player->getName());
                    }
                    break;
            }
        });
        $party = PartyProvider::getParty($player->getName());
        if (is_null($party)) return PartyForm::openDefaultPartyUI($player);
        $form->setTitle("Party");
        $form->addButton("Invite");
        $form->addButton("Settings");
        $color = ($party->getMemberCount() <= PartyProvider::$min_player) ? "§7" : '';
        if ($party->getWorldGenerate() !== true) {
            $form->addButton("Start");
        } else {
            $form->addButton("Teleport");
        }
        $form->addButton("« Exit");
        $form->sendToPlayer($player);
    }

    private static function openSettingsUI(Player $player)
    {
        $party = PartyProvider::getParty($player->getName());
        if (is_null($party)) return PartyForm::openDefaultPartyUI($player);
        $form = new SimpleForm(function (Player $player, $data) {
            $party = PartyProvider::getParty($player->getName());

            if (is_null($data) or is_null($party)) return;
            switch ($data) {
                case 0:
                    PartyForm::openListMemberUI($player);
                    break;
                case 1:
                    PartyForm::openModeUI($player);
                    break;
                case 2:
                    if ($party->getType() === "private") {
                        $party->setType("public");
                        $player->sendMessage("§a» Your party is now public.");
                    } else {
                        $player->sendMessage("§a» Your party is no longer public.");
                        $party->setType("private");
                    }
                    break;
                case 3:
                    PartyForm::openMaxPlayerSettingUI($player);
                    break;
                case 4:
                    $party->stop();
                    break;
                case 5:
                    if (!PartyProvider::hasParty($player->getName())) {
                        PartyForm::openDefaultUI($player);
                    } else {
                        if ($party->isLeader($player->getName())) {
                            PartyForm::openLeadertUI($player);
                        } else {
                            PartyForm::openMembertUI($player);
                        }
                    }
                    break;
            }
        });
        $form->setTitle("Party");
        $form->addButton("Members [" . $party->getMemberCount() ."/". $party->getMaxSlot() ."]");
        $form->addButton("Gamemode [". $party->getMode()."]");
        $form->addButton("Visibility [" . ucfirst($party->getType()) . "]");
        $form->addButton("Max Player [". $party->getMaxSlot()."]");
        $form->addButton("Delete");
        $form->addButton("« Back");
        $form->sendToPlayer($player);
    }


    private static function openMaxPlayerSettingUI(Player $player)
    {
        $party = PartyProvider::getParty($player->getName());
        if (is_null($party) or empty($party->getMember())) return PartyForm::openDefaultPartyUI($player);
        $form = new CustomForm(function (Player $player, $data) {
            if (is_null($data)) return;
            $party = PartyProvider::getParty($player->getName());
            if (!is_null($party) and isset($data[0]))
            {
                $party->setMaxSlot($data[0]);
                $player->sendMessage("§a» The maximum number of players in the party has been set to ". $data[0].".");
            }else{
                $player->sendMessage("§a» The maximum number of players in the party has been set to 2.");
            }
        });
        $form->setTitle("Party");
        $form->addSlider("Max player", 2, $party->getMaxSlotGroup(), 1, $party->getMaxSlot());

        $groupe = PlayerManager::getInformation($player->getName(), 'group');
        foreach (["Basic" => 8, "Regular" => 10, "Member" => 12, "Veteran" => 14, "Myth" => 20, "Legend" => 25] as $group => $slot)
        {
            $color = ($group === $groupe) ? '§a' : '§c';
        }
        $form->sendToPlayer($player);
    }
    private static function openListMemberUI(Player $player)
    {
        $party = PartyProvider::getParty($player->getName());
        if (is_null($party) or empty($party->getMember())) return;
        $form = new SimpleForm(function (Player $player, $data) {
            $party = PartyProvider::getParty($player->getName());
            if ($data === null) return PartyForm::openDefaultPartyUI($player);

            if ((($data == 0) ? 0 : $data + 1) == count($party->getMember()) + 1){
                if($player->getName() === $party->getLeader())
                {
                    PartyForm::openSettingsUI($player);
                }else{
                    PartyForm::openMembertUI($player);
                }
            } else{
                PartyForm::openListMemberUI($player);
            }
        });
        $form->setTitle("Party");
        foreach ($party->getMember() as $id => $info) {
            $form->addButton($info);
        }
        $form->addButton("« Exit");
        $form->sendToPlayer($player);
    }

    //Tout a été fixée normalement ici
    private static function openInvitetUI(Player $player)
    {
        $form = new CustomForm(function (Player $player, $data) {
            if ($data === null) return PartyForm::openDefaultPartyUI($player);
            $player_dat = Server::getInstance()->getPlayer($data[0]);
            if (is_null($player_dat)) return $player->sendMessage("§c» This player is not online or could not be found.");
            if ($player->getName() === $player_dat->getName()) return PartyForm::openInvitetUI($player);
            $party = PartyProvider::getParty($player->getName());
            if (is_null($party)) return PartyForm::openDefaultPartyUI($player);
            if (PartyProvider::hasParty($player_dat->getName())) return $player->sendMessage("§c» The player is already in a party..");
            if (PartyProvider::hasInvite($player_dat->getName(), $party->getId())) return $player->sendMessage("§c» Already invited player.");

            $party->addInvite($player_dat->getName());
            $player->sendMessage("§a» The invitation to join your party has been sent to " . $player_dat->getName().".");
            $player_dat->sendMessage("§a» " . $player->getName() . " sent you an invitation to join their party.");
        });

        $form->setTitle("Party");
        $form->addInput("Invite a player :", "...");
        $form->sendToPlayer($player);
    }

    private static function openMembertUI(Player $player)
    {
        $party = PartyProvider::getParty($player->getName());
        if (is_null($party)) return PartyForm::openDefaultPartyUI($player);
        $form = new SimpleForm(function (Player $player, $data) {
            if ($data === null) return;
            switch ($data) {
                case 0:
                    $party = PartyProvider::getParty($player->getName());
                    if (is_null($party)) return PartyForm::openDefaultPartyUI($player);
                    PartyForm::openListMemberUI($player);
                    break;
                case 1:
                    $party = PartyProvider::getParty($player->getName());
                    if (is_null($party)) return PartyForm::openDefaultPartyUI($player);
                    $party->removeMember($player->getName());
                    break;

                case 2:
                    $party = PartyProvider::getParty($player->getName());
                    if (is_null($party)) return PartyForm::openDefaultPartyUI($player);
                    $party->teleportPlayer($player->getName());
                    break;
            }
        });
        $form->setTitle("Party");
        $form->addButton("Members");
        $form->addButton("Leave");
        if (!is_null($party) and $party->world_generate == true) {
            $form->addButton("Teleport");
        }
        $form->sendToPlayer($player);
    }

    private static function openManageInvitetUI(Player $player)
    {
        //Si jamais il a pas d'invite
        if (empty(PartyProvider::getInvite($player->getName()))) return PartyForm::openDefaultPartyUI($player);
        $form = new SimpleForm(function (Player $player, $data) {
            if ($data === null) return;
            //Si jamais elle est pas set
            if (!isset(PartyProvider::getInvite($player->getName())[$data])) return $player->sendMessage("§c» This invitation has expired.");
            //get la bonne invite
            $invite = PartyProvider::getInvite($player->getName())[$data];
            $party = PartyProvider::getParty($invite["leader_name"]);
            if (!is_null($party)) PartyForm::openValidInviteUI($player, $invite["leader_name"]);
        });

        $form->setTitle("Party");
        //Listé les invites
        foreach (PartyProvider::getInvite($player->getName()) as $invite) {
            $form->addButton($invite["leader_name"]);
        }
        $form->sendToPlayer($player);
    }

    private static $system = [];

    private static function openValidInviteUI(Player $player, $leader)
    {
        if (is_null(PartyProvider::getParty($leader))) return PartyForm::openDefaultPartyUI($player);
        self::$system[$player->getName()] = $leader;
        $id = PartyProvider::getParty($leader)->getId();

        $form = new ModalForm(function (Player $player, $data) {
            if ($data === null) return;
            $party = PartyProvider::getParty(PartyForm::$system[$player->getName()]);

            // NOTE : Si jamais la party est plus valide
            if (is_null($party)) return $player->sendMessage("§c» This party is no longer available.");
            if ($data == 0) {
                $party->removePlayerInvite($player->getName());
                $player->sendMessage("§c» You've declined the " . $party->getLeader() . "'s invitation.");
            } else {
                if ($party->getMemberCount() >= $party->getMaxSlot()) return $player->sendMessage("§a» Party is full.");
                $party->addMember($player->getName());
                $leader = Server::getInstance()->getPlayer($party->getLeader());
                if (!is_null($leader)) $leader->sendMessage("§a» " . $player->getName() . " has accepted your party invitation.");
                $player->sendMessage("§a» You've accepted the party invitation from " . $party->getLeader().".");
            }
        });
        $form->setTitle("Party");
        $form->setContent("Are you sure to join the " . $leader . "'s party?");
        $form->setButton1("Join");
        $form->setButton2("« Exit");
        $form->sendToPlayer($player);
    }
}