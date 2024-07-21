<?php

namespace practice\loader;

use practice\Main;
use practice\skin\SkinAdapterPersona;
use pocketmine\entity\Skin;
use pocketmine\network\mcpe\protocol\types\SkinAdapterSingleton;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginException;

class SkinLoader
{
    private static $skins = [];

    /**
     * @throws PluginException
     */
    public static function load()
    {
        if(!extension_loaded('gd'))
        {
            var_dump("gd is not enabled!");
        }

        SkinAdapterSingleton::set(new SkinAdapterPersona());
        // Skins
        $skinPaths = glob(str_replace("\\", "/", Main::getInstance()->getDataFolder() . "skins/*.png"));

        if(empty($skinPaths))
        {
            var_dump("skin path is empty.");
            return;
        }

        if(is_array($skinPaths))
        {
            foreach($skinPaths as $id => $skinPath)
            {
                set_error_handler(function ($errno, $errstr, $errfile, $errline) use ($skinPath)
                {
                    var_dump("error1");
                });

                $img = imagecreatefrompng($skinPath);
                restore_error_handler();
                if($img === false)
                {
                    continue;//just continue, log via error handler above
                }

                self::$skins[] = new Skin("personatoskin." . basename($skinPath), self::fromImage($img), "", "geometry.humanoid.custom");
                @imagedestroy($img);

                if(empty(self::$skins))
                {
                    var_dump("skins array is empty");
                    return;
                }
            }
        }
    }

    /**
     * from skinapi
     * @param resource $img
     * @return string
     */
    public static function fromImage($img)
    {
        $bytes = '';
        for($y = 0; $y < imagesy($img); $y++)
        {
            for($x = 0; $x < imagesx($img); $x++)
            {
                $rgba = @imagecolorat($img, $x, $y);
                $a = ((~((int)($rgba >> 24))) << 1) & 0xff;
                $r = ($rgba >> 16) & 0xff;
                $g = ($rgba >> 8) & 0xff;
                $b = $rgba & 0xff;
                $bytes .= chr($r) . chr($g) . chr($b) . chr($a);
            }
        }
        @imagedestroy($img);
        return $bytes;
    }

    public static function getRandomSkin(): Skin
    {
        if(!empty(self::$skins))
        {
            return self::$skins[array_rand(self::$skins)];
        }
    }
}