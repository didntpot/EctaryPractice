<?php

namespace practice\duels;

use pocketmine\item\Item;
use pocketmine\level\Location;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use practice\api\KitsAPI;
use practice\api\LightningAPI;
use practice\api\PlayerDataAPI;
use practice\api\SoundAPI;
use practice\duels\Task\Delete;
use practice\duels\Task\Duel;
use practice\duels\Task\Finish;
use practice\duels\Task\Generation;
use practice\duels\Task\Wait;
use practice\events\listener\PlayerJoin;
use practice\Main;
use practice\manager\LevelManager;
use practice\manager\PlayerManager;
use practice\manager\SQLManager;
use practice\provider\WorkerProvider;
use pocketmine\math\Vector3;
use pocketmine\item\ItemFactory;
use practice\items\Fireworks;
use practice\entity\FireworksRocket;
use practice\duels\form\DuelEndForm;
use practice\scoreboard\{DuelStartScoreboard, DuelGameScoreboard, DuelEndScoreboard};

class Duels
{
    private array $players = [];
    private array $dead_players = [];
    private array $spectator_players = [];

    private string $kit = "debuff";
    private string $name = "Debuff";
    public int $duel_time = 3000;
    public int $duel_duration = 0;
    private $map;
    private string $type = "unranked";
    public int $wait_time = 3;
    public int $wait_finish_time = 2;
    private $spawn_localtion;
    private $id;
    private int $kb = 1;
    private bool $world_generate = false;
    private int $attack_delay = 20;
    private bool $pvp = true;
    private bool $teaming = true;
    private int $player_number_in_team = 2;

    /**
     * @var 0 = No Start
     * @var 1 = Generation
     * @var 2 = Wait Time
     * @var 3 = Duel Time
     * @Var 4 = Countdown finish duel
     */

    private $status = 0;

    public function __construct(array $players)
    {
        $this->id = uniqid();
        $this->players = $players;
    }

    public function start()
    {
        $this->startGeneration();
    }

    public function setTeaming(bool $teaming)
    {
        $this->teaming = $teaming;
    }

    public function isTeaming()
    {
        return $this->teaming;
    }

    public function setPlayerNumberInTeam(int $number)
    {
        $this->player_number_in_team = $number;
    }

    public function getPlayerNumberInTeam()
    {
        return $this->player_number_in_team;
    }

    public function makeTeam(array $players)
    {
        $players = $this->getPlayers();
        $team_number = count($players) % $this->getPlayerNumberInTeam();
    }

    public function stop()
    {;
        $this->teleportSpawn();
        $this->deleteWorld();
        if (isset(DuelsProvider::$duels[$this->getId()])) unset(DuelsProvider::$duels[$this->getId()]);
    }

    public function startDuel()
    {
        if (count($this->getPlayers()) > 1)
        {
            $this->unsetOldFighter();
            $this->setStatus(3);
            $this->setImmobile(false);
            $this->setGamemode($this->getPlayers(), 0);
            #$this->sendMessage("§a» The match has started.", "message");
            Main::getInstance()->getScheduler()->scheduleRepeatingTask(new Duel($this->getId()), 15);
        }else{
            $this->stop();
        }
    }

    public function startGeneration()
    {
        if (!empty($this->map) or !empty($this->spawn_localtion))
        {
            $this->setStatus(1);
            $patch = Server::getInstance()->getDataPath().DuelsProvider::WORLD_PATCH;
            if ($this->world_generate == false)
            {
                SQLManager::sendToWorker(new Generation($this->getId(), $patch, Server::getInstance()->getDataPath()."worlds", $this->getMapName()), WorkerProvider::SYSTEME_ASYNC);
            }
        }
    }

    public function deleteWorld()
    {
        $patch = $this->getMapPatch();
        if (is_dir($patch) or $this->isWorldGenerate())
        {
            Server::getInstance()->unloadLevel(Server::getInstance()->getLevelByName($this->getId()));
            SQLManager::sendToWorker(new Delete($patch), WorkerProvider::SYSTEME_ASYNC);
        }
    }

    public function teleportSpawn()
    {
        foreach($this->getAllPlayers() as $player)
        {
            $playerd =  Server::getInstance()->getPlayer($player);
            if (!is_null($playerd))
            {
                $playerd->teleport(Server::getInstance()->getDefaultLevel()->getSafeSpawn());
                KitsAPI::addLobbyKit($playerd);
                DuelEndForm::openForm($playerd);
            }
        }
    }

    public function teleportInitialPosition($y = 0)
    {
        foreach ($this->getAllPlayers() as $id => $player)
        {
            $player =  Server::getInstance()->getPlayer($player);
            if (!is_null($player))
            {
                if ($player->getY() <= $y) $player->teleport($this->getSpawnPosition()[array_rand($this->getSpawnPosition())]);
            }
        }
    }

    public function teleportPosition($players, $position)
    {
        foreach ($players as $id => $player)
        {
            $player =  Server::getInstance()->getPlayer($player);
            if (!is_null($player))
            {
                $player->removeAllWindows();
                $player->teleport($position);
            }
        }
    }

    public function teleport()
    {
        if ($this->isWorldGenerate())
        {
            foreach ($this->getAllPlayers() as $id => $player)
            {
                $player = Server::getInstance()->getPlayer($player);
                if (!is_null($player))
                {
                    $position = $this->getSpawnPosition()[$id];
                    if ($position instanceof Location)
                    {
                        if (!is_null($player)) $player->teleport($position);
                    }
                }
            }
        }
    }

    public function getKB()
    {
        return $this->kb;
    }

    public function setKB($kb)
    {
        $this->kb = $kb;
    }

    public function isSpectator($player)
    {
        return in_array($player, $this->spectator_players);
    }

    public function getSpectatorPlayers(): array
    {
        return $this->spectator_players;
    }

    public function getPlayerType($player)
    {
        if (in_array($player, $this->spectator_players)) return "spectator";
        if (in_array($player, $this->players)) return "fighter";
        if (in_array($player, $this->dead_players)) return "death";
        return null;
    }

    public function addSpectator($player)
    {
        if (!$this->isWorldGenerate() and $this->getStatus() != 3)
        {
            if (!is_null(Server::getInstance()->getPlayer($player))) Server::getInstance()->getPlayer($player)->sendMessage("World no generate.");
        }else{
            if (in_array($player, $this->getPlayers())) $this->removePlayer($player);
            if (!in_array($player, $this->spectator_players))
            {
                $this->spectator_players[] = $player;
                if (!is_null(Server::getInstance()->getPlayer($player)))
                {
                    $player = Server::getInstance()->getPlayer($player);
                    KitsAPI::clear($player, "all");
                    $player->teleport($this->getSpawnPosition()[array_rand($this->getSpawnPosition())]);
                    $player->setGamemode(3);
                    $player->getInventory()->setItem(8, Item::get(Item::REDSTONE, 0, 1)->setCustomName("§r§cLeave spectator mode"));
                    $player->getInventory()->setItem(0, Item::get(Item::COMPASS, 0, 1)->setCustomName("§r§bTeleport to a player"));
                    $this->sendMessage("§a» ". $player->getName() ." is now spectating.", "message");
                }
            }
        }
    }

    public function removeSpectator($player)
    {
        $this->sendMessage("§a» ". $player ." is no longer spectating.", "message");
        if(in_array($player, $this->getSpectatorPlayers()))
        {
            $id = array_search($player, $this->spectator_players);
            unset($this->spectator_players[$id]);

            $player = Server::getInstance()->getPlayer($player);
            if(!is_null($player))
            {
                if ($player->getLevel()->getFolderName() === $this->getId()) LevelManager::teleportSpawn($player);
                $player->setGamemode(0);
                KitsAPI::addLobbyKit($player);
            }
        }
    }

    public function isDead($player): bool
    {
        return in_array($this->dead_players, $player);
    }

    public function addDeath($player, $killer = null)
    {
        if (in_array($player->getName(), $this->getPlayers())) $this->removePlayer($player->getName());
        if (!in_array($player->getName(), $this->dead_players))
        {
            $this->dead_players[] = $player->getName();

            PlayerManager::setInformation($player->getName(), "loses", PlayerManager::getInformation($player->getName(), "loses") + 1, false);

            if (!is_null($killer))
            {
                $str = PlayerManager::getInformation($killer->getName(), "kill_streak") + 1;
                $killer->sendMessage("§a» Your kill-streak is now: $str");

                if (PlayerManager::getInformation($player->getName(), "kill_streak")) {
                    $player->sendMessage("§c» You've lost your kill-streak.");
                    PlayerManager::setInformation($player->getName(), "kill_streak", 0, false);
                }

                PlayerDataAPI::setKillDeathStreak($killer->getName(),
                    PlayerManager::getInformation($killer->getName(), "kill") + 1,
                    PlayerManager::getInformation($killer->getName(), "kill_streak") + 1,
                    $player->getName(),
                    PlayerManager::getInformation($player->getName(), "death") + 1);
            }

            if ($this->getType() === "ranked")
            {
                $rand = rand(1, 3);
                $elo = ((PlayerManager::getInformation($player->getName(), "elo") - $rand) <= 0) ? 0 : PlayerManager::getInformation($player->getName(), "elo") - $rand;
                PlayerManager::setInformation($player->getName(), "elo", $elo, false);
                $player->addTitle("§r", "§7-§f$rand");
            }
        }
    }

    public function getDeadplayers()
    {
        return $this->dead_players;
    }

    public function addKit()
    {
        foreach ($this->players as $player)
        {
            if (!is_null(Server::getInstance()->getPlayer($player)))
            {
                DuelsProvider::addKit(Server::getInstance()->getPlayer($player), $this->getKit());

                unset(PlayerJoin::$scoreboard[$player]);
                PlayerJoin::$scoreboard[$player] = new DuelGameScoreboard(Server::getInstance()->getPlayer($player));
                PlayerJoin::$scoreboard[$player]->sendRemoveObjectivePacket();
                DuelGameScoreboard::createLines(Server::getInstance()->getPlayer($player));
            }
        }
    }

    public function getKit()
    {
        return $this->kit;
    }

    public function setKit($kit)
    {
        $this->kit = $kit;
    }


    public function startFinish()
    {
        $this->setStatus(4);
        //On tp les joueurs qui sont en dessous de Y9
        $this->teleportInitialPosition(0);
        //On tp les morts + les spectator a une pos
        $this->teleportPosition($this->getSpectatorPlayers(), $this->getSpawnPosition()[array_rand($this->getSpawnPosition())]);
        //On set le gm 0 a tt le monde
        $this->setGamemode($this->getAllPlayers(), 0);
        //On clear l'inventaire
        #$this->clearInventory($this->getAllPlayers());
        $this->setImmobile(false);
        Main::getInstance()->getScheduler()->scheduleRepeatingTask(new Finish($this->getId()), 20);
    }

    public function startWait()
    {
        if ($this->world_generate == true)
        {
            if($this->isTeaming())
            {
                $this->makeTeam($this->getPlayers());
                $this->teleport();
                if($this->getKit() == "sumo" or $this->getKit() == "oneline" or $this->getKit() == "hikabrain" or $this->getKit() == "thebridge" or $this->getKit() == "mlgrush")
                {
                    $this->setImmobile();
                }
                $this->clearInventory($this->getPlayers());
                $this->setStartScoreboard($this->getPlayers());
                Main::getInstance()->getScheduler()->scheduleRepeatingTask(new Wait($this->getId()), 20);
                $this->setStatus(2);
            }else{
                $this->teleport();
                if($this->getKit() == "sumo" or $this->getKit() == "oneline" or $this->getKit() == "hikabrain" or $this->getKit() == "thebridge" or $this->getKit() == "mlgrush")
                {
                    $this->setImmobile();
                }
                $this->clearInventory($this->getPlayers());
                $this->setStartScoreboard($this->getPlayers());
                Main::getInstance()->getScheduler()->scheduleRepeatingTask(new Wait($this->getId()), 20);
                $this->setStatus(2);
            }
        }else{
            Server::getInstance()->getLogger()->info("The world has not been loaded.");
            $this->stop();
        }


    }

    public function setStartScoreboard(array $players)
    {
        foreach ($players as $player)
        {
            $player = Server::getInstance()->getPlayer($player);
            if (!is_null($player))
            {
                unset(PlayerJoin::$scoreboard[$player->getName()]);
                PlayerJoin::$scoreboard[$player->getName()] = new DuelStartScoreboard($player);
                PlayerJoin::$scoreboard[$player->getName()]->sendRemoveObjectivePacket();
                DuelStartScoreboard::createLines($player);
            }
        }
    }

    public function getOpponent($player)
    {
        foreach ($this->getPlayers() as $player_op)
        {
            if ($player_op !== $player)
            {
                return $player_op;
            }
        }
    }

    public function removePlayer($player_name)
    {

        if (in_array($player_name, $this->getPlayers())) unset($this->players[array_search($player_name, $this->players)]);
        if ($this->isSpectator($player_name)) $this->removeSpectator($player_name);
    }

    public function spawnFirework($name)
    {
        if (!is_null($name))
        {
            $player = Server::getInstance()->getPlayer($name);

            if (!is_null($player))
            {
                $fw = ItemFactory::get(Item::FIREWORKS);

                switch(PlayerDataAPI::getSetting($name, "victory_fireworks_color"))
                {
                    case "white":
                        $fw->addExplosion(Fireworks::TYPE_HUGE_SPHERE, Fireworks::COLOR_WHITE, "", false, false);
                        break;
                    case "black":
                        $fw->addExplosion(Fireworks::TYPE_HUGE_SPHERE, Fireworks::COLOR_BLACK, "", false, false);
                        break;
                    case "red":
                        $fw->addExplosion(Fireworks::TYPE_HUGE_SPHERE, Fireworks::COLOR_RED, "", false, false);
                        break;
                    case "blue":
                        $fw->addExplosion(Fireworks::TYPE_HUGE_SPHERE, Fireworks::COLOR_BLUE, "", false, false);
                        break;
                    case "green":
                        $fw->addExplosion(Fireworks::TYPE_HUGE_SPHERE, Fireworks::COLOR_GREEN, "", false, false);
                        break;
                    case "yellow":
                        $fw->addExplosion(Fireworks::TYPE_HUGE_SPHERE, Fireworks::COLOR_YELLOW, "", false, false);
                        break;

                    default:
                        $fw->addExplosion(Fireworks::TYPE_CREEPER_HEAD, Fireworks::COLOR_GREEN, "", false, false);
                        break;
                }

                $fw->setFlightDuration(1.2);

                $level = $player->getLevel();
                $vector3 = $player->getPosition();

                if(!is_null($level))
                {
                    $nbt = FireworksRocket::createBaseNBT($vector3, new Vector3(0.001, 0.05, 0.001), lcg_value() * 360, 90);
                    $entity = FireworksRocket::createEntity("FireworksRocket", $level, $nbt, $fw);

                    if($entity instanceof FireworksRocket)
                    {
                        $entity->spawnTo($player);
                    }
                }
            }
        }
    }

    public function getDisplayName($name)
    {
        if (is_null($name)) return "Unknown";
        return ((is_null(Server::getInstance()->getPlayer($name))) ? "Unknown" : (empty(Server::getInstance()->getPlayer($name)->getDisplayName()))) ? "Unknown" : Server::getInstance()->getPlayer($name)->getDisplayName();
    }

    public function addStatsWinner($player)
    {
        $player = Server::getInstance()->getPlayer($player);

        if (!is_null($player))
        {
            unset(PlayerJoin::$scoreboard[$player->getName()]);
            PlayerJoin::$scoreboard[$player->getName()] = new DuelEndScoreboard($player);
            PlayerJoin::$scoreboard[$player->getName()]->sendRemoveObjectivePacket();
            DuelEndScoreboard::createLines($player);
            
            PlayerManager::setInformation($player->getName(), "wins", PlayerManager::getInformation($player->getName(), "wins") + 1, false);

            if(PlayerManager::getInformation($player->getName(), "wins") == 10) $player->sendMessage("§a» You have now unlocked the access to §lRanked§r§a queue.");

            if ($this->getType() === "ranked")
            {
                $rand = rand(3, 5);
                if($player->hasPermission("elo.boost")) $rand = rand(6, 9);
                PlayerManager::setInformation($player->getName(), "elo", PlayerManager::getInformation($player->getName(), "elo") + $rand, false);
                $player->addTitle("§r", "§7+§f$rand");

            }
        }
    }

    public function setWinner($player, $opo)
    {
        Server::getInstance()->broadcastMessage("§7". $this->getDisplayName($player) ." won an ". $this->getType()." ". $this->getName()." match against ". $this->getDisplayName($opo) .".");
        $this->sendMessage("§aWinner: §e". $this->getDisplayName($player)." §7- §cLoser: §e". $this->getDisplayName($opo)."", "message");

        foreach ([$player, $opo] as $name)
        {
            $this->spawnFirework($name);
        }

        $this->addStatsWinner($player);
        $this->startFinish();
    }

    public function setGamemode(array $players, $gamemode)
    {
        foreach ($players as $player)
        {
            $player = Server::getInstance()->getPlayer($player);
            if (!is_null($player)) $player->setGamemode($gamemode);
        }
    }

    public function sendTitle(array $players, $message)
    {
        foreach ($players as $player)
        {
            $player = Server::getInstance()->getPlayer($player);
            if (!is_null($player))
            {
                $player->sendTitle($message,"§r", 20, 1, 20);
            }
        }
    }

    public function sendMessage($message, $type)
    {
        foreach ($this->getAllPlayers() as $player)
        {
            $player = Server::getInstance()->getPlayer($player);
            if (!is_null($player)){
                switch ($type)
                {
                    case "message":
                        $player->sendMessage($message);
                        break;
                    case "popup":
                        $player->sendPopup($message);
                        break;
                }
            }
        }
    }

    public function sendSound($sound)
    {
        foreach ($this->getAllPlayers() as $player)
        {
            if(PlayerDataAPI::getSetting($player, "duel_sounds") === "true")
            {
                if (!is_null(Server::getInstance()->getPlayer($player))) SoundAPI::playSound(Server::getInstance()->getPlayer($player), $sound);
            }
        }
    }

    public function clearInventory(array $players)
    {
        foreach ($players as $player)
        {
            $player = Server::getInstance()->getPlayer($player);
            if (!is_null($player)) KitsAPI::clear($player, "all");
        }
    }

    public function setImmobile($status = true)
    {
        foreach ($this->players as $player)
        {
            $player = Server::getInstance()->getPlayer($player);
            if (!is_null($player)) $player->setImmobile($status);
        }
    }

    public function setDuelTime(int $time)
    {
        $this->duel_time = $time;
    }

    public function getDuelTime()
    {
        return $this->duel_time;
    }

    public function setMap($map)
    {
        $this->map = $map;
    }

    public function getMap()
    {
        return Server::getInstance()->getLevelByName($this->map);
    }

    public function getMapName()
    {
        return $this->map;
    }

    public function getMapPatch()
    {
        return str_replace("\\", "/", Server::getInstance()->getDataPath()."worlds/".$this->getId());
    }

    public function setWaitTime(int $time)
    {
        $this->wait_time = $time;
    }

    public function getWaitTime(): int
    {
        return $this->wait_time;
    }

    public function getPlayers(): array
    {
        return $this->players;
    }

    public function getAllPlayers()
    {
        return array_merge($this->getPlayers(), $this->getSpectatorPlayers(), $this->getDeadplayers());
    }

    public function setPlayers(array $players): void
    {
        $this->players = $players;
    }

    public function setSpawnPositions(array $locations)
    {
        $this->spawn_localtion = $locations;
    }

    public function getSpawnPosition()
    {
        if (!isset($this->spawn_localtion)) return null;
        $loc = [];

        foreach ($this->spawn_localtion as $id => $location)
        {
            $level = Server::getInstance()->getLevelByName($this->getId());
            if (is_null($level))
            {
                Server::getInstance()->loadLevel($this->getId());
                Server::getInstance()->getLevelByName($this->getId())->setAutoSave(false);
            }
            $loc[] = new Location($location["x"], $location["y"], $location["z"], 0.0, 0.0, Server::getInstance()->getLevelByName($this->getId()));
        }
        if (empty($loc)) return null;
        return $loc;
    }

    public function getId(): string
    {
        if (!isset($this->id)) $this->id = uniqid();
        return $this->id;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function isWorldGenerate(): bool
    {
        return $this->world_generate;
    }


    public function setWorldGenerate(bool $world_generate): void
    {
        $this->world_generate = $world_generate;
    }

    public function getWaitFinishTime(): int
    {
        return $this->wait_finish_time;
    }

    public function setWaitFinishTime(int $wait_finish_time): void
    {
        $this->wait_finish_time = $wait_finish_time;
    }

    public function saveId()
    {
        $patch = str_replace("\\", "/", Server::getInstance()->getDataPath().DuelsProvider::WORLD_PATCH);
        $config = new Config($patch."/id.json");
        $config->set($this->getId());
        $config->save();
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type): void
    {
        $this->type = $type;
    }

    public function setAttackDelay($attack_delay)
    {
        $this->attack_delay = $attack_delay;
    }

    public function getAttackDelay()
    {
        return $this->attack_delay;
    }

    public function setPvpEnable($pvp)
    {
        $this->pvp = $pvp;
    }

    public function getPvpEnable()
    {
        return $this->pvp;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    private function unsetOldFighter()
    {
        foreach ($this->getPlayers() as $player)
        {
            if (isset(PlayerManager::$fighter[$player])) unset(PlayerManager::$fighter[$player]);
            if (isset(PlayerManager::$fighter[$player])) PlayerManager::$combat_time[$player] = 15;
        }
    }

    /**
     * @param string $id
     * @return Duels
     */
    public function setId(string $id): Duels
    {
        $this->id = $id;
        return $this;
    }
}