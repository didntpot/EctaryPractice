<?php


namespace practice\manager;

use Exception;
use mysqli;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Config;
use practice\api\SyncAPI;
use practice\Main;
use practice\provider\LoginProvider;
use practice\provider\WorkerProvider;
use practice\tasks\async\AsyncQuerryMYSQL;

class SQLManager
{

    /**
     * Cache SYSTEM CONFIGURATION ECTARY S4
     * !! All not used !!
     */
    const DEFAULT = ["permissions" => "",
        "group" => "Basic",
        "language" => null,
        "kill" => 0,
        "death" => 0,
        "division" => "§8[Bronze I]",
        "elo" => 1000,
        "kill_streak" => 0,
        "wins" => 0,
        "loses" => 0,
        "tags" => null,
        "coins" => 0,
        "cape_select" => null,
        "block_select" => null,
        "setting" => "scoreboard:true,particles_amplifier:0,potions_counter:default,cps_counter:false,potions_colour:default,reach_counter:false,combo_counter:false,auto_rekit:true,auto_sprint:false,bow_hit_sound:orb,lightning_death:false,arm_swing_sound:false,private_message:true,duel_requests:true,msg_sounds:false,duel_sounds:true,victory_fireworks_color:white",
        "join_date" => 0,
        "serveur" => null,
        "proxy" => 0,
        "ip" => null,
        "platform_device" => "Unknown",
        "id_device" => null,
        "id_kit" => null,
        "nodebuff_kit" => null,
        "debuff_kit" => null,
        "build_kit" => null,
        "builduhc_kit" => null,
        "finaluhc_kit" => null,
        "caveuhc_kit" => null,
        "pitchout_kit" => null,
        "hg_kit" => null,
        "mlgrush_kit" => null,

        "ban_ip" => "",
        "ban_xuid" => "",
        "ban_id_device" => "",
        "ban_region" => "NO_FOUND",
        "ban_type" => 1,
        "ban_expire" => 0,
        "ban_reason" => "No Reason",
        "unban" => 0,
        "unban_name" => "Unknown",

        "expire_mute" => 0,
        "mute" => 1,
        "author_mute" => "Unknown",
        "date_mute" => 0,
        "unmute_status" => 0,
        "unmute_author" => "Unknown"
    ];

    public static $sql;
    public static $cache = [];

    public static function initSQL()
    {
        if (!isset(self::$sql)) {
            try {
                $sql = new mysqli(LoginProvider::IP, LoginProvider::LOGIN, LoginProvider::PASSWORD, LoginProvider::DB_NAME);
                self::$sql = $sql;
                SQLManager::initAllDataBase();
            } catch (Exception $exception) {
                Server::getInstance()->getLogger()->warning("[Ectary] " . $exception->getMessage());
            }
        }
    }

    public static function initAllDataBase()
    {
        $db = SQLManager::getSQLSesion();
        PlayerManager::initTablePlayers($db);
        GroupManager::initTableGroup($db);
        TempRankManager::initTempRankSQL($db);
        BanManager::initBanSQL($db);
        KitsManager::initKitsSQL($db);
        TagsManager::initTableTags($db);
        CapesManager::initTableCapes($db);
        MuteManager::iniMuteSQL($db);
        $db->close();
    }

    public static function getSQLSesion(): mysqli
    {
        return self::$sql;
    }

    public static function getSQLSesionAsync(): mysqli
    {
        try {
            return new mysqli(LoginProvider::IP, LoginProvider::LOGIN, LoginProvider::PASSWORD, LoginProvider::DB_NAME);
        } catch (Exception $exception) {
           echo "[EctaryCore] " . $exception->getMessage()."\n";
        }
    }

    public static function getPlayerCache(string $name, $default = false)
    {
        if ($default == true) return SQLManager::DEFAULT;
        if (!isset(SQLmanager::$cache[$name])) return null;
        return SQLmanager::$cache[$name];
    }

    public static function setPlayerCache(string $player_name, array $cache)
    {
        if (!isset(SQLmanager::$cache[$player_name])) SQLmanager::$cache[$player_name] = SQLManager::DEFAULT;
        SQLmanager::$cache[$player_name] = $cache;
    }

    public static function sendToWorker(AsyncTask $asyncTask, int $worker)
    {
        if ($worker > Server::getInstance()->getAsyncPool()->getSize()) return Server::getInstance()->getLogger()->warning("Worker no exist !!");
        Server::getInstance()->getAsyncPool()->submitTask($asyncTask);
    }

    public static function mysqlQuerry($querry)
    {
        SQLManager::sendToWorker(new AsyncQuerryMYSQL($querry), WorkerProvider::SYSTEME_ASYNC);
    }
}