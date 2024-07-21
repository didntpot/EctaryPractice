<?php

namespace practice\party;

use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Config;
use practice\api\KitsAPI;
use practice\duels\Duels;
use practice\duels\Task\Delete;
use practice\manager\LevelManager;
use practice\manager\SQLManager;
use practice\provider\WorkerProvider;

class PartySystem
{
    public $leader;
    public $member = [];
    public $type = "private";
    public $world_generate = false;
    public $party_id;
    public $mode = null;
    public $world_name;
    public $max_player;
    public $max_slot_group;
    public $start_time;

    public function __construct()
    {
        $this->party_id = uniqid(5);
        $this->start_time = time();
    }


    public function setLeader(Player $player)
    {
        $this->leader = $player->getName();
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setMode(string $mode)
    {
        $this->mode = $mode;
    }

    public function getMode()
    {
        return $this->mode;
    }


    public function updateMode($mode, $world): bool
    {
        if ($this->world_generate == true) $this->deleteWorld();
        $this->mode = $mode;
        $this->world_generate = false;
        $this->world_name = $world;
        $this->sendMessage("§a» This party gamemode has been set to " . $mode.".");
        return true;
    }


    public function stop()
    {
        //rerirer toute les invite quand la party se stop
        $this->sendMessage("§c» The party has been deleted.");
        $this->removeAllInvite($this->leader);
        $this->removePlayerParty();
        $this->teleportSpawn();
        PartyProvider::deleteParty($this->leader);
        if ($this->world_generate == true) {
            $this->deleteWorld();
            Server::getInstance()->getLogger()->info("§c» The ". $this->leader."'s party has been deleted. (". count($this->getMember())." players)");
        }
    }

    public function start()
    {
        if ($this->world_generate == false) {
            if (is_null($this->getWorldName())) {
                $this->sendMessage("§a» An error with the world has been detected.");
                $this->stop();
                return;
            }

            $patch = PartyProvider::getWorldPatch($this->party_id, $this->getWorldName());
            $this->sendMessage("§a» The party will be starting soon...");
            Server::getInstance()->getAsyncPool()->submitTaskToWorker(new PartySystemAsync($patch[0], $patch[1], $this->leader), 0);
        } elseif ($this->world_generate == true) {
            $this->teleportWorld();
        }
    }

    public function isLeader(string $name)
    {
        if ($this->leader === $name) return true;
        return false;
    }

    public function deleteWorld()
    {
        $patch = str_replace("\\", "/", Server::getInstance()->getDataPath() . "worlds\\" . $this->getId());
        $this->teleportSpawn();
        $level = Server::getInstance()->getLevelByName($this->getId());
        if (!is_null($level)) Server::getInstance()->unloadLevel($level);
        SQLManager::sendToWorker(new Delete($patch), WorkerProvider::SYSTEME_ASYNC);
    }

    public function getId()
    {
        return $this->party_id;
    }

    public function getLeader()
    {
        return $this->leader;
    }

    public function getMemberCount(): int
    {
        $member = $this->member;
        array_push($member, $this->getLeader());
        return count($member);
    }

    public function addMember(string $name)
    {
        if (!in_array($name, $this->member) or $name === $this->leader) array_push($this->member, $name);
        PartyProvider::setParty($name, $this);
        $this->removePlayerInvite($name);
    }

    public function getMember(): array
    {
        $member = $this->member;
        array_push($member, $this->getLeader());
        return $member;
    }

    public function removeMember(string $name)
    {
        foreach ($this->member as $id => $member) {
            if ($name === $member) {
                if (Server::getInstance()->getPlayer($name)->isOnline()) {
                    LevelManager::teleportSpawn(Server::getInstance()->getPlayer($name));
                }
                unset($this->member[$id]);
                PartyProvider::deleteParty($name);

                if ($this->getMemberCount() == 1)
                {
                    $this->sendMessage('§c» You are all alone in your party.');
                    $this->deleteWorld();
                }
            }
        }
    }

    public function addKit($name)
    {
        $player = Server::getInstance()->getPlayer($name);
        if (!is_null($player)) {
            switch ($this->getMode()) {
                case "Nodebuff":
                    KitsAPI::addNodebuffKit($player);
                    break;
                case "Classic":
                    KitsAPI::addClassicKit($player);
                    break;
                case "Gapple":
                    KitsAPI::addGappleKit($player);
                    break;
                case "Unknown":
                    $player->sendMessage("§c» No kit was found...");
                    break;
                default :
                    KitsAPI::clear($player, "all");
                    break;
            }
        }
    }

    public function addInvite(string $name)
    {
        if (!PartyProvider::hasInvite($name, $this->getId())) {
            PartyProvider::$invite[$name][] = ["party_id" => $this->getId(), "leader_name" => $this->leader];
        }
    }


    public function removePlayerInvite(string $name)
    {
        if (isset(PartyProvider::$invite[$name])) {
            foreach (PartyProvider::$invite[$name] as $id => $item) {
                if ($item["party_id"] === $this->party_id) {
                    unset(PartyProvider::$invite[$name][$id]);
                    if (empty(PartyProvider::$invite[$name])) unset(PartyProvider::$invite[$name]);
                }
            }
        }
    }

    public function removeAllInvite(string $name)
    {
        if (!empty(PartyProvider::$invite)) {
            foreach (PartyProvider::$invite as $id => $value) {
                foreach ($value as $id_2 => $item) {
                    if ($item["party_id"] === $this->party_id) unset(PartyProvider::$invite[$id][$id_2]);
                    if (empty(PartyProvider::$invite[$id])) unset(PartyProvider::$invite[$id]);
                }
            }
        }
    }

    public function removePlayerParty()
    {
        foreach (PartyProvider::$party as $member => $party) {
            if ($party->getId() === $this->getId()) {
                Server::getInstance()->getLogger()->info("§c» $member removed.");
                unset(PartyProvider::$party[$member]);
            }
        }
    }

    public function sendMessageToLeader(string $message)
    {
        if (!is_null(Server::getInstance()->getPlayer($this->leader))) Server::getInstance()->getPlayer($this->leader)->sendMessage($message);
    }

    public function sendMessage(string $message)
    {
        if (!is_null(Server::getInstance()->getPlayer($this->leader))) Server::getInstance()->getPlayer($this->leader)->sendMessage($message);
        foreach ($this->member as $member) {
            $player = Server::getInstance()->getPlayer($member);
            if (!is_null($player)) $player->sendMessage($message);
        }
    }

    public function teleportWorld()
    {
        $pos = Server::getInstance()->getLevelByName($this->party_id)->getSpawnLocation();
        if (!is_null($pos)) {
            if (!is_null(Server::getInstance()->getPlayer($this->leader))) {
                $this->world_generate = true;
                if (Server::getInstance()->getPlayer($this->leader)->getLevel()->getFolderName() !== $this->getId()) {
                    Server::getInstance()->getPlayer($this->leader)->teleport($pos);
                    $this->addKit($this->leader);
                }
            }

            foreach ($this->member as $member) {
                $player = Server::getInstance()->getPlayer($member);
                if (!is_null($player) and $player->getLevel()->getFolderName() !== $this->getId()) {
                    $player->teleport($pos);
                    $this->addKit($player->getName());
                }
            }
        } else {
            $this->sendMessage("§c» He got an error while teleporting.");
        }
    }

    public function teleportPlayer(string $name)
    {
        $player = Server::getInstance()->getPlayer($name);
        $level = Server::getInstance()->getLevelByName($this->party_id);
        if (!is_null($player) and $player->getLevel()->getFolderName() !== $this->getId()) {
            if (is_null($level)) return $player->sendMessage("§c» Error loading party world.");
            $pos = $level->getSpawnLocation();
            $player->teleport($pos);
            $this->addKit($player->getName());
        }
    }

    public function teleportSpawn()
    {
        if (!is_null(Server::getInstance()->getPlayer($this->leader))) {
            $this->world_generate = true;
            LevelManager::teleportSpawn(Server::getInstance()->getPlayer($this->leader));
            KitsAPI::clear(Server::getInstance()->getPlayer($this->leader), "all");
            KitsAPI::addLobbyKit(Server::getInstance()->getPlayer($this->leader));
        }

        foreach ($this->member as $member) {
            $player = Server::getInstance()->getPlayer($member);
            if (!is_null($player))
            {
                KitsAPI::clear($player, "all");
                LevelManager::teleportSpawn($player);
                KitsAPI::addLobbyKit($player);
            }
        }
    }

    public function setWorldGenerate(bool $true)
    {
        $this->world_generate = $true;
    }

    public function getWorldGenerate()
    {
        return $this->world_generate;
    }

    public function setWorldName($name)
    {
        $this->world_name = $name;
    }

    public function getWorldName()
    {
        if (!isset($this->world_name)) return null;
        return $this->world_name;
    }


    public function getMaxSlot(): int
    {
        return (empty($this->max_player)) ? $this->getMaxSlotGroup() : $this->max_player;
    }

    public function setMaxSlot(int $max_player): void
    {
        $this->max_player = $max_player;
    }

    public function getMaxSlotGroup(): int
    {
        return (empty($this->max_slot_group)) ? 4 : (int) $this->max_slot_group;
    }

    public function setMaxSlotGroup(int $max_slot_group)
    {
        $this->max_slot_group = $max_slot_group;
    }
}

class PartySystemAsync extends AsyncTask
{
    private $from;
    private $to;
    private $leader;

    public function __construct($from, $to, string $leader)
    {
        $this->from = $from;
        $this->to = $to;
        $this->leader = $leader;
    }

    public function onRun()
    {
        if (PartySystemAsync::custom_copy($this->to, $this->from)) {
            $this->setResult(true);
        } else {
            $this->setResult(false);
        }
    }

    public function onCompletion(Server $server)
    {
        $party = PartyProvider::getParty($this->leader);
        if (!is_null($party)) {
            if ($this->getResult()) {
                $config = new Config(str_replace("\\", "/", Server::getInstance()->getDataPath() . "worlds\\PartyMap\\worlds.yml"), Config::YAML);
                $old = $config->getAll();
                if (!isset($old[$party->getId()]))
                {
                    $old[] = $party->getId();
                    $config->setAll($old);
                    $config->save();
                }

                if (!is_null(Server::getInstance()->getPlayer($this->leader))) {
                    if (!Server::getInstance()->isLevelLoaded($party->getId())) {
                        Server::getInstance()->loadLevel($party->getId());
                        Server::getInstance()->getLogger()->info("§a» The ". $this->leader." party has been created.");
                        $party->teleportWorld();
                    }
                }
            } else {
                $party->sendMessage("§c» A generation error occurred, contact an administrator...");
            }
        } else {
            if (!is_null(Server::getInstance()->getPlayer($this->leader))) {
                Server::getInstance()->getPlayer($this->leader)->sendMessage("§c» The party had an error, disconnect and reconnect to solve the problem.");
            }
        }
    }

    public static function custom_copy($src, $dst)
    {
        if (!is_dir($src)) return false;
        $dir = opendir($src);
        @mkdir($dst);
        foreach (scandir($src) as $file) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    self::custom_copy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
        return true;
    }
}