<?php


namespace practice\api;


use pocketmine\Player;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;
use practice\manager\SQLManager;
use practice\provider\WorkerProvider;

class InformationAPI
{

    const LOBBY = "ectary.club";
    const EU = "ectary.club";
    const EU2 = "eu2.ectary.club";
    const NA = "na.ectary.club";
    const SA = "sa.ectary.club";
    const AS = "as.ectary.club";
    const AU = "au.ectary.club";
    const DEV = "ectary.club";
    const DB = "db.ectary.club";

    const LOBBY_PORT = "19132";

    const EU_PORT = "8070";
    const EU2_PORT = "8071";
    const EU3_PORT = "8082";
    const EU4_PORT = "8074";
    const EU5_PORT = "8075";

    const NA_PORT = "17674";
    const NA2_PORT = "19141";
    const NA3_PORT = "19143";

    const SA_PORT = "7170";
    const SA2_PORT = "7171";

    const AS_PORT = "17675";
    const AS2_PORT = "17679";

    const AU_PORT = "9190";
    const AU2_PORT = "9191";

    const DEV_PORT = "19180";

    public static $region;
    public static $reportRegion;

    public static $ip;

    public static function initRegion()
    {
        if (!isset(self::$region)) {
            $ip = Internet::getIP();
            if ($ip === InformationAPI::getIp(self::EU, DNS_A) and (string) Server::getInstance()->getPort() === self::EU_PORT) {
                self::$region = "EU";
                self::$reportRegion = "EU1";
            } elseif ($ip === InformationAPI::getIp(self::NA, DNS_A) and (string) Server::getInstance()->getPort() === self::NA_PORT) {
                self::$region = "NA";
                self::$reportRegion = "NA1";
            } elseif ($ip === InformationAPI::getIp(self::LOBBY, DNS_A) and (string) Server::getInstance()->getPort() === self::LOBBY_PORT) {
                self::$region = "LOBBY";
            } elseif ($ip === InformationAPI::getIp(self::DEV, DNS_A) and (string) Server::getInstance()->getPort() === self::DEV_PORT) {
                self::$region = "DEV";
            } elseif ($ip === InformationAPI::getIp(self::AS, DNS_A) and (string) Server::getInstance()->getPort() === self::AS_PORT) {
                self::$region = "AS";
                self::$reportRegion = "AS1";
            } elseif ($ip === InformationAPI::getIp(self::SA, DNS_A) and (string) Server::getInstance()->getPort() === self::SA_PORT) {
                self::$region = "SA";
                self::$reportRegion = "SA1";
            } elseif ($ip === InformationAPI::getIp(self::AU, DNS_A) and (string) Server::getInstance()->getPort() === self::AU_PORT) {
                self::$region = "AU";
                self::$reportRegion = "AU1";
            } elseif ($ip === InformationAPI::getIp(self::AU, DNS_A) and (string) Server::getInstance()->getPort() === self::AU2_PORT) {
                self::$region = "AU";
                self::$reportRegion = "AU2";
            } elseif ($ip === InformationAPI::getIp(self::NA, DNS_A) and (string) Server::getInstance()->getPort() === self::NA2_PORT) {
                self::$region = "NA";
                self::$reportRegion = "NA2";
            } elseif ($ip === InformationAPI::getIp(self::AS, DNS_A) and (string) Server::getInstance()->getPort() === self::AS2_PORT) {
                self::$region = "AS";
                self::$reportRegion = "AS2";
            } elseif ($ip === InformationAPI::getIp(self::SA, DNS_A) and (string) Server::getInstance()->getPort() === self::SA2_PORT) {
                self::$region = "SA";
                self::$reportRegion = "SA2";
            } elseif ($ip === InformationAPI::getIp(self::EU, DNS_A) and (string) Server::getInstance()->getPort() === self::EU2_PORT) {
                self::$region = "EU";
                self::$reportRegion = "EU2";
            } elseif ($ip === InformationAPI::getIp(self::EU, DNS_A) and (string) Server::getInstance()->getPort() === self::EU3_PORT) {
                self::$region = "EU";
                self::$reportRegion = "EU3";
            } elseif ($ip === InformationAPI::getIp(self::NA, DNS_A) and (string) Server::getInstance()->getPort() === self::NA2_PORT) {
                self::$region = "NA";
                self::$reportRegion = "NA2";
            } elseif ($ip === InformationAPI::getIp(self::EU2, DNS_A) and (string) Server::getInstance()->getPort() === self::EU4_PORT) {
                self::$region = "EU";
                self::$reportRegion = "EU4";
            } elseif ($ip === InformationAPI::getIp(self::EU2, DNS_A) and (string) Server::getInstance()->getPort() === self::EU5_PORT) {
                self::$region = "EU";
                self::$reportRegion = "EU5";
            }
        }
        self::$ip = Internet::getIP();

        Server::getInstance()->getLogger()->info("[Practice] Server region has been set to " . self::getServerRegion());
    }


    public static function getServerRegion()
    {
        if (!isset(self::$region)) {
            return "NA";
        } else {
            return self::$region;
        }
    }

    public static function getServerReportRegion()
    {
        if (!isset(self::$reportRegion)) {
            return "NA";
        } else {
            return self::$reportRegion;
        }
    }

    public static function getIp($dns, $type)
    {
        $dns = @dns_get_record($dns, $type);
        if (empty($dns)) return null;
        return $dns[0]["ip"];
    }

    public static function sendAllServerStatus()
    {
        SQLManager::sendToWorker(new InformationAsync([self::EU . ":" . self::EU_PORT, self::NA . ":" . self::NA_PORT, self::AS . ":" . self::AS_PORT,self::LOBBY . ":" . self::LOBBY_PORT]), WorkerProvider::COMMAND_ASYNC);
    }
}

class InformationAsync extends AsyncTask
{

    private $ip;
    private $player;

    public function __construct(array $ip, ?Player $player = null)
    {
        $this->ip = $ip;
        $this->player = $player;
    }

    public function onRun()
    {
        $arrContextOptions = array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
            ),
        );

        $server = [];

        foreach ($this->ip as $ip => $info) {
            $status = json_decode(@file_get_contents("https://api.mcsrvstat.us/2/$info", false, stream_context_create($arrContextOptions)));
            $server[$info] = $status;
        }
        $this->setResult($server);
    }


    public function onCompletion(Server $server)
    {
    }
}