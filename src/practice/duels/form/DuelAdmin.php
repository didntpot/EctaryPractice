<?php


namespace practice\duels\form;


use pocketmine\Player;
use practice\api\form\SimpleForm;
use practice\duels\Duels;
use practice\duels\DuelsProvider;
use practice\duels\manager\DuelsManager;
use practice\manager\TimeManager;

class DuelAdmin
{
    public static array $duel = [];

    public static function openAdminDuel($player)
    {
        self::$duel[$player->getName()] = DuelsManager::getAllDuelById();

        $form = new SimpleForm(function (Player $player, $data){
            if (is_null($data))
            {
                unset(self::$duel[$player->getName()]);
                return;
            }
            if ((($data == 0) ? 0 : $data + 1) == count(self::$duel[$player->getName()]) + 1) return;
            $duel = self::$duel[$player->getName()][$data];

            if ($duel instanceof Duels)
            {
                if (in_array($duel->getStatus(), [0, 1, 2])) return $player->sendMessage("§c» This match is not started yet.");
                if ($duel->getStatus() == 4) return $player->sendMessage("§c» This match has already ended.");
                self::openAdminDuelOption($player, $duel->getId());

            }

            unset(self::$duel[$player->getName()]);
        });

        $form->setTitle("Admin");
        if (empty(self::$duel[$player->getName()]))
        {
            $form->setContent("There no matches currently running.");
        }else{
            foreach (self::$duel[$player->getName()] as $id => $item)
            {
                if ($item instanceof Duels)
                {
                    $players = implode(" vs ", $item->getPlayers());
                    $form->addButton("$players\n". $item->getType()." | ". $item->getKit() . " | ". $item->getId());
                }
            }
        }
        $form->addButton("« Exit");
        $form->sendToPlayer($player);
    }

    public static array $duel_id_form = [];
    public static function openAdminDuelOption(Player $player, $duel_id)
    {
        self::$duel_id_form[$player->getName()] = $duel_id;
        $form = new SimpleForm(function (Player $player, $data){
            if (is_null($data)) return;
            if (isset(DuelsProvider::$duels[self::$duel_id_form[$player->getName()]]))
            {
                $duel = DuelsProvider::$duels[self::$duel_id_form[$player->getName()]];
                if ($duel instanceof Duels)
                {
                    switch ($data)
                    {
                        case 0:
                            $pos = $duel->getSpawnPosition()[array_rand($duel->getSpawnPosition())];
                            if (is_null($pos))
                            {
                                $player->sendMessage("§c» You have been teleported into the ".  implode(", ", $duel->getPlayers()) ." duel (". $duel->getId().").");
                                $player->teleport($pos);
                            }
                            break;
                        case 1:
                            $player->sendMessage(implode(" vs ", $duel->getPlayers()). "\n§c» Id: ". $duel->getId() ."\n§c» Map: ". $duel->getMapName()."\n§c» Kit: ". $duel->getKit()."\n§c» Time left: ". TimeManager::secondsToTime($duel->getDuelTime()));
                            break;
                        case 2:
                            break;
                    }
                }
            }

            unset(self::$duel_id_form[$player->getName()]);
        });
        $form->setTitle("Admin");
        $form->addButton("Teleport");
        $form->addButton("Information");
        $form->addButton("Delete");
        $form->addButton("« Exit");
    }
}