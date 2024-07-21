<?php

namespace practice;

use pocketmine\Player;
use pocketmine\entity\Attribute;
use pocketmine\entity\Entity;
use practice\duels\manager\DuelsManager;
use practice\entity\Hook;
use practice\manager\KnockbackManager;
use JD\Main;
use pocketmine\Server;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;

class PracticePlayer extends Player
{
    protected $fishing = null;
    public $scoreboardRound = "§7www.ectary.club";
    public $scoreboard = "";

    public function startFishing($obj):void
    {
        if($this->isOnline()){

            if(!$this->isFishing())
            {
                $this->fishing=$obj;
            }
        }
    }

    public function getFishing()
    {
        return $this->fishing;
    }

    public function stopFishing(bool $click = true, bool $killEntity = true):void
    {
        if($this->isFishing()) $this->fishing = null;
    }

    public function isFishing():bool
    {
        return $this->fishing!==null;
    }

    public function attack(EntityDamageEvent $source):void
    {
        parent::attack($source);
        if($source->isCancelled())
        {
            return;
        }
        if($source instanceof EntityDamageByEntityEvent)
        {
            $damager = $source->getDamager();

            if($damager instanceof Player)
            {
                if(DuelsManager::isInDuel($damager->getName()))
                {
                    $duel = DuelsManager::getDuel($damager->getName());

                    if($duel->getKit() == "combo")
                    {
                        $this->attackTime = 2;
                    }else{
                        $this->attackTime = 10;
                    }

                }else{
                    if($this->getLevel()->getName() == "sumo" or $this->getLevel()->getName() == "fist1" or $this->getLevel()->getName() == "fist2")
                    {
                        $this->attackTime = 8;
                    }elseif($this->getLevel()->getName() == "combo")
                    {
                        $this->attackTime = 2;
                    }else{
                        $this->attackTime = 10;
                    }
                }
            }
        }
    }

    public function knockBack($damager, float $damage, float $x, float $z, float $base=0.4):void
    {
        $xzKB = 0.387; #0.375
        $yKb = 0.388; #0.390
        if($damager instanceof Player)
        {
            if(DuelsManager::isInDuel($damager->getName()))
            {
                $duel = DuelsManager::getDuel($damager->getName());

                if($duel->getKit() === "combo")
                {
                    $xzKB = 0.210;
                    $yKb = 0.150;
                }

                if($duel->getKit() === "mlgrush")
                {
                    if($damager->getInventory()->getItemInHand()->getId() == 369)
                    {
                        $xzKB = 0.700;
                        $yKb = 0.400;
                    }
                }
            }elseif($damager->getLevel()->getName() == "combo"){
                $xzKB = 0.210;
                $yKb = 0.150;
            }elseif($damager->getLevel()->getName() == "sumo"){
                $xzKB = 0.365;
                $yKb = 0.390; #395
            }else{
                if($damager->getLevel()->getName() == "fist1" or $damager->getLevel()->getName() == "fist2")
                {
                    $xzKB = 0.365;
                    $yKb = 0.390; #395
                }

                if($damager->getLevel()->getName() == "pitchout")
                {
                    if($damager->getInventory()->getItemInHand()->getId() == 369)
                    {
                        $xzKB = 0.900;
                        $yKb = 0.600;
                    }
                }else{
                    $xzKB = 0.375;
                    $yKb = 0.390;
                }
            }
        }

        $f = sqrt($x * $x + $z * $z);

        if($f <= 0)
        {
            return;
        }


        if(mt_rand() / mt_getrandmax() > $this->getAttributeMap()->getAttribute(Attribute::KNOCKBACK_RESISTANCE)->getValue())
        {
            $f =1 / $f;
            $motion = clone $this->motion;
            $motion->x /= 2;
            $motion->y /= 2;
            $motion->z /= 2;
            $motion->x += $x * $f * $xzKB;
            $motion->y += $yKb;
            $motion->z += $z * $f * $xzKB;

            if($motion->y > $yKb)
            {
                $motion->y = $yKb;
            }

                $this->setMotion($motion);
        }
    }

    public $reachFlags = 0;
    public $cpsFlags = 0;

    public function getFlags($flag)
    {
        switch($flag)
        {
            case "reach":

                return $this->reachFlags;

                break;

            case "cps":

                return $this->cpsFlags;

                break;
        }
    }

    public function flagToStaff()
    {
        if($this->reachFlags == 3)
        {
            foreach(Server::getInstance()->getOnlinePlayers() as $staff)
            {
                if($staff->hasPermission("staff.command"))
                {
                    $staff->sendMessage("§f[§6J.D.§f] §c{$this->getName()} §7has been flagged for reach. ({$this->getPing()}ms)");
                    $this->resetFlag("reach");
                }
            }
        }

        if($this->cpsFlags == 3)
        {
            $cps = Main::getInstance()->getClickHandler()->getCps($this);

            foreach(Server::getInstance()->getOnlinePlayers() as $staff)
            {
                if($staff->hasPermission("staff.command"))
                {
                    $staff->sendMessage("§f[§6J.D.§f] §c{$this->getName()} §7has been flagged for autoclick. ({$this->getPing()}ms) ($cps cps)");
                    $this->resetFlag("cps");
                }
            }
        }
    }

    public function addFlag($flagType)
    {
        switch($flagType)
        {
            case "reach":

                if($this->reachFlags !== 3)
                {
                    $this->reachFlags = $this->reachFlags+1;
                }else{
                    $this->flagToStaff();
                }

                break;

            case "cps":

                if($this->cpsFlags !== 3)
                {
                    $this->cpsFlags = $this->cpsFlags+1;
                }else{
                    $this->flagToStaff();
                }

                break;
        }
    }

    public function resetAllFlags()
    {
        $this->reachFlags = 0;
    }

    public function resetFlag($flagType)
    {
        switch($flagType)
        {
            case "reach":

                $this->reachFlags = 0;

                break;

            case "cps":

                $this->cpsFlags = 0;

                break;
        }
    }
}