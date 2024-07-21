<?php

namespace practice\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use practice\api\form\CustomForm;
use practice\api\form\SimpleForm;
use practice\api\PlayerDataAPI;
use practice\manager\GroupManager;
use practice\manager\PlayerManager;
use practice\manager\SQLManager;
use practice\manager\TagsManager;
use practice\manager\TempRankManager;
use practice\provider\WorkerProvider;

class Setting extends PluginCommand
{
    public function __construct($plugin)
    {
        parent::__construct("setting", $plugin);
        $this->setDescription("Setting command");
        $this->setPermission("setting.command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if ($sender instanceof Player) {
            if (!$sender->hasPermission($this->getPermission())) return;
            $this->openGroupUI($sender);
        }
    }

    private function openGroupUI(Player $player)
    {
        $form = new SimpleForm(function (Player $player, $data) {
            if (is_null($data)) return;
            switch ($data) {
                case 0:
                    if ($player->hasPermission("reload_group.command")) {
                        GroupManager::initGroupCache();
                        $player->sendMessage("§a» Group information is in initialization.");
                    } else {
                        $this->openGroupUI($player);
                    }
                    break;
                case 1:
                    TagsManager::initCacheTags();
                    $player->sendMessage("§a» Tags information is in initialization.");
                    break;
                case 2:
                    $this->openReloadPlayerUI($player);
                    #todo: Add Reload Player Function
                    break;
                case 3:
                    $this->openGroupListUI($player);
                    break;
                case 4:
                    $this->openSetGroup($player);
                    break;
                case 6:
                    $this->openTransferDataUI($player);
                    break;
                case 7:
                    $this->openSetGroupTemp($player);
                    break;
            }
        });
        $form->setTitle("Setting Panel");

        $form->addButton("Reload Groups");  #0
        $form->addButton("Reload Tags");    #1
        $form->addButton("Reload Player Permissions");  #2
        $form->addButton("Groups list\n(" . count(GroupManager::getGroupCache()) . ")"); #3
        $form->addButton("Set Group");  #4
        $form->addButton("Get Info");   #5
        $form->addButton("Transfer Info");   #6
        $form->addButton("Set Temp Group");   #7

        $form->sendToPlayer($player);
    }

    public function openTransferDataUI(Player $player)
    {
        $form = new CustomForm(function (Player $player, $data) {
            if (is_null($data)) return;
            if (empty($data[0]) or empty($data[1])) return $this->openTransferDataUI($player);
            $old = $data[0];
            $new = $data[1];

            $data = (!is_null(Server::getInstance()->getPlayer($old))) ? SQLManager::getPlayerCache(Server::getInstance()->getPlayer($old)->getName(), false) : SQLManager::getPlayerCache($old);
            if (!$player->hasPermission("transfer.ui")) return;
            Server::getInstance()->getAsyncPool()->submitTaskToWorker(new DataTransferAsync($player->getName(), $old, $new, $data), WorkerProvider::COMMAND_ASYNC);
        });
        $form->setTitle("Transfer");
        $form->addInput("Old Name:");
        $form->addInput("New Name:");
        $form->addToggle("Reset old data");
        $form->sendToPlayer($player);
    }

    private $group = [];

    public function openSetGroup(Player $player)
    {
        $groups = GroupManager::getAllGroupName();
        if (empty($groups)) return $this->openGroupUI($player);

        $form = new CustomForm(function (Player $player, $data) {
            if (is_null($data)) return;
            $player_send = Server::getInstance()->getPlayer($data[0]);
            if (is_null($player)) {
                return $player->sendMessage("§c» The player is not online");
            }
            $group = $this->group[$player->getName()][$data[1]];
            PlayerManager::setInformation($player_send->getName(), "group", $group);
            $player->sendMessage("§a» The rank of " . $player_send->getName() . " has been updated ($group)");
        });
        $form->setTitle("Group");
        $form->addInput("Player name:");
        $form->addDropdown("Group list", $groups);
        $this->group[$player->getName()] = $groups;
        $form->sendToPlayer($player);
    }

    public function openGroupListUI(Player $player)
    {
        $groups = GroupManager::getGroupCache();
        if (empty($groups)) return $this->openGroupUI($player);

        $form = new CustomForm(function (Player $player, $data) {
            $this->openGroupUI($player);
        });
        $form->setTitle("Groups List");
        $form->addLabel("List of groups :");
        foreach ($groups as $name => $info) {
            $form->addLabel($name . " (" . count(GroupManager::getPlayersGroup($name)) . " player(s) online)");
        }
        $form->sendToPlayer($player);
    }

    public function openReloadPlayerUI(Player $player)
    {
        $form = new CustomForm(function (Player $player, $data) {
            if (is_null($data)) return;
            $player_send = Server::getInstance()->getPlayer($data[0]);
            if (is_null($player)) return $player->sendMessage("§c» The player is not online");
            GroupManager::reloadPlayerPermission($player_send->getName());
            $player->sendMessage("§a» " . $player_send->getName() . " permissions have been updated.");
        });
        $form->setTitle("Reload Permissions");
        $form->addInput("Player name:");
        $form->sendToPlayer($player);
    }

    public function openSetGroupTemp(Player $player)
    {
        $groups = GroupManager::getAllGroupName();
        if (empty($groups)) return $this->openGroupUI($player);

        $form = new CustomForm(function (Player $player, $data) {
            if (is_null($data)) return;
            $player_send = Server::getInstance()->getPlayer($data[0]);
            if (is_null($player_send)) return $player->sendMessage("§c» The player is not online");
            if (empty($data[2])) return $this->openSetGroupTemp($player);
            if (!strtotime($data[2])) return $this->openSetGroupTemp($player);
            $group = $this->group[$player->getName()][$data[1]];

            TempRankManager::setOldGroup($player->getName(), PlayerManager::getInformation($player->getName(), "group"));
            TempRankManager::setTimeGroup($player->getName(), strtotime($data[2]));
            TempRankManager::setTempGroup($player_send->getName(), $group, strtotime($data[2]));

            $player->sendMessage("§a» The temp rank of " . $player_send->getName() . " has been set ($group)");
        });
        $form->setTitle("Temp Group");
        $form->addInput("Player name:");
        $form->addDropdown("Group list", $groups);
        $form->addInput("Time", "1min, 20day, etc");
        $this->group[$player->getName()] = $groups;
        $form->sendToPlayer($player);
    }
}


class DataTransferAsync extends AsyncTask
{

    private $old;
    private $new;
    private $name;
    private $data;

    public function __construct($name, $old, $new, $data = [])
    {
        $this->old = $old;
        $this->new = $new;
        $this->name = $name;
        $this->data = $data;
    }

    public function onRun()
    {
        $db = SQLManager::getSQLSesionAsync(); #DATABASE CONNECT

        //Si jamais il a des infos en local
        if (!is_null($this->data)) {
            $result_old_player = $this->data;
        } else {
            $prep = $db->prepare('SELECT * FROM `players` WHERE `name` = "' . $this->old . '"');
            if (is_bool($prep->get_result())) return $this->setResult(["type" => "player_no_found"]); #Player no found in DB
            $result_old_player = $prep->get_result()->fetch_all(MYSQLI_ASSOC); #Old player data
        }

        $prep = $db->prepare('SELECT id FROM `players` WHERE `name` = "' . $this->new . '"'); #CHECK NEW PLAYER EXIST

        //So jamais il a jamais été register le crée
        var_dump($prep->get_result()->fetch_all());
        if (is_bool($prep->get_result())) {
            #WORKING
            echo "Create\n";
            //Crée le joueur dans la db
            $prep = $db->prepare('INSERT
                                            INTO
                                              `players`(
                                                `name`,
                                                `permissions`,
                                                `group`,
                                                `language`,
                                                `kill`,
                                                `death`,
                                                `elo`,
                                                `kill_streak`,
                                                `wins`,
                                                `loses`,
                                                `tags`,
                                                `coins`,
                                                `cape_select`,
                                                `block_select`,
                                                `join_date`,
                                                `platform_device`
                                              )
                                            VALUES(
                                              "' . $this->new . '",
                                              "' . $result_old_player["permissions"] . '",
                                              "' . $result_old_player["group"] . '",
                                              "' . $result_old_player["language"] . '",
                                              "' . $result_old_player["kill"] . '",
                                              "' . $result_old_player["death"] . '",
                                              "' . $result_old_player["elo"] . '",
                                              "' . $result_old_player["kill_streak"] . '",
                                              "' . $result_old_player["wins"] . '",
                                              "' . $result_old_player["loses"] . '",
                                              "' . $result_old_player["tags"] . '",
                                              "' . $result_old_player["coins"] . '",
                                              "' . $result_old_player["cape_select"] . '",
                                              "' . $result_old_player["block_select"] . '",
                                              "' . $result_old_player["join_date"] . '",
                                              "' . $result_old_player["platform_device"] . '"
                                            )');
            $prep->execute();
        } else {
            echo "Update\n";
            //Update le joueur dans la db
            $prep = $db->prepare('UPDATE `players` SET `permissions`="' . $result_old_player["permissions"] . '",`group`="' . $result_old_player["group"] . '",`language`="' . $result_old_player["language"] . '",`kill`="' . $result_old_player["kill"] . '",`death`="' . $result_old_player["death"] . '",`elo`="' . $result_old_player["elo"] . '",`kill_streak`="' . $result_old_player["kill_streak"] . '",`wins`="' . $result_old_player["wins"] . '",`loses`="' . $result_old_player["loses"] . '",`tags`="' . $result_old_player["tags"] . '",`coins`="' . $result_old_player["coins"] . '",`cape_select`="' . $result_old_player["cape_select"] . '",`block_select`="' . $result_old_player["block_select"] . '",`join_date`="' . $result_old_player["join_date"] . '",`platform_device`="' . $result_old_player["platform_device"] . '" WHERE name = "' . $this->new . '"'); #UPDATE NEW PLAYER HERE
            $prep->execute();
        }
        $db->close();
    }

    public function onCompletion(Server $server)
    {
        switch ($this->getResult()["type"]) {
            case "player_no_found":
                $player = Server::getInstance()->getPlayer($this->name);
                if (!is_null($player)) $player->sendMessage("Player " . $this->old . " no found.");
                #TODO : CREATE FORM TO CREATE PLAYER IN DB
                break;
            case "transfer_account":
                #TODO : TRANSFER MESSAGE
                break;
        }
    }
}