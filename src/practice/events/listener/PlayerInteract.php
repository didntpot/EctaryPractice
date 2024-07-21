<?php

namespace practice\events\listener;

use practice\forms\{
    FFAForm,
    PlayerPerksForm,
    LeaderboardsForm, EventsForm, KitEditorForm
};
use pocketmine\tile\Sign;
use practice\api\KitsAPI;
use practice\duels\form\DuelsForm;
use practice\duels\form\DuelSpectateForm;
use practice\manager\KitsManager;
use practice\manager\PlayerManager;
use practice\api\ArmorAPI;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\item\Armor;
use pocketmine\block\Block;
use pocketmine\Server;
use practice\manager\SQLManager;
use practice\party\form\PartyForm;
use pocketmine\math\Vector3;
use practice\manager\LevelManager;
use practice\Main;
use practice\api\gui\InvMenu;
use practice\manager\TimeManager;

class PlayerInteract implements Listener
{
    public static array $cooldown = [];
    public static array $soupChestCooldown = [];

    public function onInteract(PlayerInteractEvent $event)
    {
        $player = $event->getPlayer();
        $block = $event->getBlock();

        if($player->getLevel()->getName() == "spawn" && $player->getInventory()->getItemInHand()->getId() == 325) return $event->setCancelled();
        if($player->getLevel()->getName() == "spawn" && $player->getInventory()->getItemInHand()->getId() == 259) return $event->setCancelled();
        if (PlayerManager::isSync($player->getName())) return;

        if (!isset(PlayerInteract::$cooldown[$player->getLowerCaseName()]) or PlayerInteract::$cooldown[$player->getLowerCaseName()] <= time()) {
            if($player->getLevel()->getName() !== "soup")
            {
                PlayerInteract::$cooldown[$player->getLowerCaseName()] = time() + 1;
                self::openForm($player, $player->getInventory()->getItemInHand()->getCustomName());
            }
        }

        if($player->getLevel()->getName() == "soup" && $event->getBlock()->getId() == 54)
        {
            $event->setCancelled();
            if(!isset(PlayerInteract::$cooldown[$player->getLowerCaseName()]) or PlayerInteract::$cooldown[$player->getLowerCaseName()] <= time())
            {
                if(!isset(PlayerInteract::$soupChestCooldown[$player->getLowerCaseName()]) or PlayerInteract::$soupChestCooldown[$player->getLowerCaseName()] <= time())
                {
                    PlayerInteract::$cooldown[$player->getLowerCaseName()] = time() + 1;
                    PlayerInteract::$soupChestCooldown[$player->getLowerCaseName()] = time() + 120;
                }else{
                    $player->sendMessage("§cThis is currently on cooldown.");
                }
            }
        }

        $os = PlayerManager::$os[$player->getName()];

        $array = ["Android", "iOS"];

        if(in_array($os, $array) and $event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK)
        {
            if($player->getInventory()->getItemInHand()->getId() === Item::SPLASH_POTION)
            {
                $player->getInventory()->getItemInHand()->onClickAir($player, $player->getDirectionVector());

                if(!$player->isCreative())
                {
                    $player->getInventory()->setItem($player->getInventory()->getHeldItemIndex(), Item::get(0));
                }
            }
        }

        $tile = $block->getLevel()->getTile($block);

            if($tile instanceof Sign)
            {
                $text = $tile->getLine(0);

                switch($text)
                {
                    case "§a§lSave.§r":

                        if(isset(KitsAPI::$isEditing[$player->getName()]))
                        {
                            switch(KitsAPI::$isEditing[$player->getName()])
                            {
                                case "NoDebuff":

                                    $content = $player->getInventory()->getContents();
                                    $player->sendMessage("§a» Your kit has been saved.");

                                    KitsManager::setKits($player->getName(), "nodebuff", $content);
                                    break;

                                case "Debuff":

                                    $content = $player->getInventory()->getContents();

                                    KitsManager::setKits($player->getName(), "debuff", $content);

                                    $player->sendMessage("§a» Your kit has been saved.");

                                    break;

                                case "Build":

                                    $content = $player->getInventory()->getContents();

                                    KitsManager::setKits($player->getName(), "build", $content);

                                    $player->sendMessage("§a» Your kit has been saved.");

                                    break;

                                case "BuildUHC":

                                    $content = $player->getInventory()->getContents();

                                    KitsManager::setKits($player->getName(), "builduhc", $content);

                                    $player->sendMessage("§a» Your kit has been saved.");

                                    break;

                                case "FinalUHC":

                                    $content = $player->getInventory()->getContents();

                                    KitsManager::setKits($player->getName(), "finaluhc", $content);

                                    $player->sendMessage("§a» Your kit has been saved.");

                                    break;

                                case "CaveUHC":

                                    $content = $player->getInventory()->getContents();

                                    KitsManager::setKits($player->getName(), "caveuhc", $content);

                                    $player->sendMessage("§a» Your kit has been saved.");

                                    break;

                                case "PitchOut":

                                    $content = $player->getInventory()->getContents();

                                    KitsManager::setKits($player->getName(), "pitchout", $content);

                                    $player->sendMessage("§a» Your kit has been saved.");

                                    break;

                                case "HG":

                                    $content = $player->getInventory()->getContents();

                                    KitsManager::setKits($player->getName(), "hg", $content);

                                    $player->sendMessage("§a» Your kit has been saved.");

                                    break;

                                case "MLG Rush":

                                    $content = $player->getInventory()->getContents();

                                    KitsManager::setKits($player->getName(), "mlgrush", $content);

                                    $player->sendMessage("§a» Your kit has been saved.");

                                    break;
                            }
                        }

                        break;

                    case "§c§lLeave.§r":
                        LevelManager::teleportSpawn($player);
                        $player->setImmobile(false);
                        unset(KitsAPI::$isEditing[$player->getName()]);
                        break;

                    case "§8§lReset.§r":
                        if(isset(KitsAPI::$isEditing[$player->getName()]))
                        {
                            switch(KitsAPI::$isEditing[$player->getName()])
                            {
                                case "NoDebuff":

                                    KitsAPI::addNodebuffKit($player, true);
                                    $player->getArmorInventory()->clearAll();
                                    $player->sendMessage("§a» Your kit has been reset.");
                                    break;

                                case "Debuff":

                                    unset(KitsAPI::$debuffKit[$player->getName()]);

                                    KitsAPI::addDebuffKit($player, true);
                                    $player->getArmorInventory()->clearAll();

                                    $player->sendMessage("§a» Your kit has been reset.");

                                    break;

                                case "Build":

                                    unset(KitsAPI::$buildKit[$player->getName()]);

                                    KitsAPI::addBuildKit($player, true);
                                    $player->getArmorInventory()->clearAll();

                                    $player->sendMessage("§a» Your kit has been reset.");

                                    break;

                                case "BuildUHC":

                                    unset(KitsAPI::$builduhcKit[$player->getName()]);

                                    KitsAPI::addBuildUHCKit($player, true);
                                    $player->getArmorInventory()->clearAll();

                                    $player->sendMessage("§a» Your kit has been reset.");

                                    break;

                                case "FinalUHC":

                                    unset(KitsAPI::$finaluhcKit[$player->getName()]);

                                    KitsAPI::addFinalUHCKit($player, true);
                                    $player->getArmorInventory()->clearAll();

                                    $player->sendMessage("§a» Your kit has been reset.");

                                    break;

                                case "CaveUHC":

                                    unset(KitsAPI::$caveuhcKit[$player->getName()]);

                                    KitsAPI::addCaveUHCKit($player, true);
                                    $player->getArmorInventory()->clearAll();

                                    $player->sendMessage("§a» Your kit has been reset.");

                                    break;

                                case "PitchOut":

                                    unset(KitsAPI::$pitchoutKit[$player->getName()]);

                                    KitsAPI::addPitchoutKit($player, true);
                                    $player->getArmorInventory()->clearAll();

                                    $player->sendMessage("§a» Your kit has been reset.");

                                    break;

                                case "HG":

                                    unset(KitsAPI::$hgKit[$player->getName()]);

                                    KitsAPI::addHGKit($player, true);
                                    $player->getArmorInventory()->clearAll();

                                    $player->sendMessage("§a» Your kit has been reset.");

                                    break;

                                case "MLG Rush":

                                    unset(KitsAPI::$mlgrushKit[$player->getName()]);

                                    KitsAPI::addMLGRushKit($player, true);
                                    $player->getArmorInventory()->clearAll();

                                    $player->sendMessage("§a» Your kit has been reset.");

                                    break;
                            }
                        }
                        break;
                }
            }


        if($player->getLevel()->getName() == "kitroom")
        {
            $event->setCancelled();
        }

        if($player->getLevel()->getName() == "pitchout")
        {
            if($player->getY() > 83)
            {
                $event->setCancelled();
            }
        }


        $block = $player->getLevel()->getBlock($player->floor()->subtract(0, 1));
        $block1 = $player->getLevel()->getBlock($player->floor()->subtract(0, 2));

        if($block->getId() == 99 && $block->getDamage() == 14)
        {
            $event->setCancelled();
        }

        if($event->getItem()->getCustomName() == "§r§bLeap")
        {
            if($player->getLevel()->getName() == "kitroom") return;
            $player->setMotion(new Vector3(0, 1.3, 0));
            $player->getInventory()->setItemInHand(Item::get(0, 0, 0));
        }

        if($block1->getId() == 99 && $block1->getDamage() == 14)
        {
            $event->setCancelled();
        }

        if($event->getItem()->getId() == 386)
        {
            $event->setCancelled();
        }

        if ($event->getItem()->getId() === 282 and $player->getHealth() != 20)
        {
            $player->getInventory()->setItemInHand(Item::get(0, 0, 0));
            $player->setHealth($player->getHealth() + 7);

            #$item = Item::get(281, 0, 1);
            #$entity = $player->getLevel()->dropItem($player->getPosition(), $item);
            #$entity->setPickupDelay(10000);
            #Main::getInstance()->getScheduler()->scheduleRepeatingTask(new SoupTask($entity), 20);
        }

        if (in_array($event->getBlock()->getId(), [145, 116, 167, 96, 54, 146]) and in_array($player->getLevel()->getName(), ["spawn"])) $event->setCancelled();

        if (($event->getAction() === PlayerInteractEvent::RIGHT_CLICK_AIR or $event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK) and ($event->getItem() instanceof Armor or $event->getItem()->getId() === Item::ELYTRA) and $event->getBlock()->getId() !== Block::ITEM_FRAME_BLOCK) {
            ArmorAPI::setArmorByType($event->getItem(), $event->getPlayer());
            $event->setCancelled(true);
        }
    }

    public static function openForm($player, $type)
    {
        switch ($type) {
            case "§r§bUnranked Queue §7(Right Click)":
                DuelsForm::openUnrankedForm($player);
                #$player->getInventory()->clearAll();
                #$player->getInventory()->addItem(Item::get(267, 0, 1)->setCustomName("§r§b1v1 §7(Right Click)"));
                #$player->getInventory()->addItem(Item::get(283, 0, 1)->setCustomName("§r§b2v2 §7(Right Click)"));
                #$player->getInventory()->setItem(8, Item::get(331, 0, 1)->setCustomName("§r§cLeave §7(Right Click)"));
                break;
            case "§r§bEdit Kits §7(Right Click)":
                KitEditorForm::openForm($player);
                break;
            case "§r§cLeave §7(Right Click)":
                KitsAPI::addLobbyKit($player);
                break;
            case "§r§b1v1 §7(Right Click)":
                DuelsForm::openUnrankedForm($player);
                break;
            case "§r§bRanked Queue §7(Right Click)":
                if($player->getPing() > 119) return $player->sendMessage("§c» Your ping must be under 120ms to play ranked.");

                if(PlayerManager::getInformation($player->getName(), "wins") < 10)
                {
                    $wins = PlayerManager::getInformation($player->getName(), "wins");
                    $more = 10-$wins;
                    return $player->sendMessage("§c» You need $more more unranked win to play ranked.");
                }

                DuelsForm::openRankedForm($player);
                break;
            case "§r§b2v2 §7(Right Click)":
                DuelsForm::openUnranked2Form($player);
                break;
            case "§r§bFFA §7(Right Click)":
                FFAForm::openForm($player);
                break;
            case "§r§bSettings §7(Right Click)":
                PlayerPerksForm::openForm($player);
                break;
            case "§r§bStats §7(Right Click)":
                PlayerPerksForm::openStatsForm($player);
                break;
            case "§r§bLeaderboards §7(Right Click)":
                LeaderboardsForm::openLbSelectionForm($player);
                break;
            case "§r§bRandom teleportation":
                $rtp = Server::getInstance()->getOnlinePlayers()[array_rand(Server::getInstance()->getOnlinePlayers(), 1)];
                if($rtp->getName() === $player->getName()) return $player->sendMessage("§c» An error has occured: You can't teleport to yourself.");
                $player->teleport($rtp);
                $player->sendMessage("§a» You've been teleported to {$rtp->getName()}.");
                break;
            case "§r§bPing Checker":
                $player->sendMessage("§a» Hit a player to check their ping.");
                break;
            case "§r§bFreeze a player":
                $player->sendMessage("§a» Hit a player to freeze them.");
                break;
            case "§r§bEnable Staff Chat":
                $player->getInventory()->setItemInHand(Item::get(351, 1, 1)->setCustomName("§r§bDisable Staff Chat"));
                PlayerManager::$staff_chat[$player->getName()] = true;
                $player->sendMessage("§a» Staff Chat is now enabled.");
                break;
            case "§r§bDisable Staff Chat":
                $player->getInventory()->setItemInHand(Item::get(351, 10, 1)->setCustomName("§r§bEnable Staff Chat"));
                PlayerManager::$staff_chat[$player->getName()] = false;
                $player->sendMessage("§a» Staff Chat is now disabled.");
                break;
            case "§r§bParty §7(Right Click)":
                PartyForm::openDefaultPartyUI($player);
                break;
            case "§r§bSpectate §7(Right Click)":
                return $player->sendMessage("§cTemporary disabled.");
                DuelSpectateForm::openSpectateForm($player);
                break;
            case "§r§bEvents":
                EventsForm::openForm($player);
                break;
        }
    }
}

class SoupTask extends \pocketmine\scheduler\Task
{
    public function __construct($item)
    {
        $this->item = $item;
    }

    public $timer = 1;

    public function onRun(int $currentTick)
    {
        $item = $this->item;

        if(!is_null($item) && $item->isAlive() && !$item->isOnFire())
        {
            if($this->timer == 0)
            {
                $item->kill();
                $item->__destruct();
                Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
            }else{
                $this->timer--;
            }
        }else{
            Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
        }
    }
}