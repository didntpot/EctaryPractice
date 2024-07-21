<?php


namespace practice\manager;


use pocketmine\Player;
use pocketmine\Server;
use practice\api\KitsAPI;
use practice\Main;

class KitsManager
{

    public static function convertKits($inventory)
    {
        return serialize($inventory);
    }

    public static function setKits($name, $kit, $inventory)
    {
        if (is_null(PlayerManager::getInformation($name, "id_kit")))
        {
            SQLManager::mysqlQuerry('INSERT INTO `kits`(`name_kit`, `'. $kit.'_kit`) VALUES ("'. $name.'", "'. base64_encode(serialize($inventory)).'")');
        }else{
            SQLManager::mysqlQuerry('UPDATE `kits` SET `'. $kit.'_kit` = "'. base64_encode(serialize($inventory)) .'" WHERE `name_kit` = "'. $name.'"');
        }
        PlayerManager::setInformation($name, $kit."_kit", base64_encode(serialize($inventory)), false);
        if (is_null(PlayerManager::getInformation($name, 'id_kit'))) PlayerManager::setInformation($name, 'id_kit', " ", false);
    }

    public static function getKits($name, $kit)
    {
        /**
         * BLANK = NO KIT SAVED
         * NULL = NO KIT ENTER
         */
        if (is_null(PlayerManager::getInformation($name, $kit."_kit")) or PlayerManager::getInformation($name, $kit."_kit") === " ")
        {
            return null;
        } else {
            return unserialize(base64_decode(PlayerManager::getInformation($name, $kit."_kit")));
        }
    }

    public static function initKitsSQL(\mysqli $db)
    {
        $prep_groups = $db->prepare('CREATE TABLE `EctaryS4`.`kits`(
                                                `id_kit` INT NOT NULL AUTO_INCREMENT,
                                                `name_kit` TEXT(9999) DEFAULT NULL,
                                                `nodebuff_kit` TEXT(9999) DEFAULT NULL,
                                                `debuff_kit` TEXT(9999) DEFAULT NULL,
                                                `build_kit` TEXT(9999) DEFAULT NULL,
                                                `builduhc_kit` TEXT(9999) DEFAULT NULL,
                                                `finaluhc_kit` TEXT(9999) DEFAULT NULL,
                                                `caveuhc_kit` TEXT(9999) DEFAULT NULL,
                                                `pitchout_kit` TEXT(9999) DEFAULT NULL,
                                                `hg_kit` TEXT(9999) DEFAULT NULL,
                                                `mlgrush_kit` TEXT(9999)DEFAULT NULL,
                                                PRIMARY KEY(`id_kit`)
                                            ) ENGINE = InnoDB;');


        if (empty($db->error)) {
            $startTime = microtime(true);
            $prep_groups->execute();
            $endTime = microtime(true);
            Main::getInstance()->getLogger()->info("The kits table has been initialized. (" . round(($endTime - $startTime) * 1000, 2) . "ms)");
        } else {
            Server::getInstance()->getLogger()->warning("[Practice] " . $db->error);
        }
    }
}