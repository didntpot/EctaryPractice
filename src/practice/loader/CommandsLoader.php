<?php

namespace practice\loader;

use practice\Main;
use practice\commands\{Alias,
    Ban,
    Database,
    Duel,
    Group,
    Mute,
    Party,
    Serverstats,
    Setgroup,
    Setting,
    Spawn,
    Tags,
    Unban,
    Worlds,
    Setknockback,
    Rekit,
    Lobby,
    Ping,
    Restart,
    Report,
    vote\BetterVoting,
    Coord,
    Menu,
    Nick,
    Kick,
    Staff,
    Dm,
    Tp,
    Tpall,
    Gm};
use practice\game\events\EventCommand;
use pocketmine\Server;

class CommandsLoader
{
    public static function initCommands()
    {
        $commands = [
            "mixer",
            "gc",
            "title",
            "xp",
            "me",
            "banlist",
            "spawnpoint",
            "extractplugin",
            "kill",
            "checkperm",
            "ver",
            "msg",
            "kick",
            "ban",
            "unban",
            "unban-ip",
            "tp",
            "gamemode",
            "defaultgamemode"
        ];

        $count = 0;
        $map = Server::getInstance()->getCommandMap();

        foreach ($commands as $cmd) {
            $command = $map->getCommand($cmd);

            if ($command !== null) {
                $command->setLabel("old_" . $cmd);
                $map->unregister($command);
                $count += 1;

            }
        }

        //Commande des joueurs
        Server::getInstance()->getCommandMap()->register("spawn", new Spawn(Main::getInstance()));
        Server::getInstance()->getCommandMap()->register("rekit", new Rekit(Main::getInstance()));
        Server::getInstance()->getCommandMap()->register("lobby", new Lobby(Main::getInstance()));
        #Server::getInstance()->getCommandMap()->register("party", new Party(Main::getInstance()));
        Server::getInstance()->getCommandMap()->register("ping", new Ping(Main::getInstance()));
        Server::getInstance()->getCommandMap()->register("report", new Report(Main::getInstance()));
        Server::getInstance()->getCommandMap()->register("vote", new BetterVoting(Main::getInstance()));
        #Server::getInstance()->getCommandMap()->register("menu", new Menu(Main::getInstance()));
        Server::getInstance()->getCommandMap()->register("nick", new Nick(Main::getInstance()));
        Server::getInstance()->getCommandMap()->register("event", new EventCommand(Main::getInstance()));

        //Commande du staff ou de gestion
        Server::getInstance()->getCommandMap()->register("setting", new Setting(Main::getInstance()));
        Server::getInstance()->getCommandMap()->register("database", new Database(Main::getInstance()));
        Server::getInstance()->getCommandMap()->register("setknockback", new Setknockback(Main::getInstance()));
        Server::getInstance()->getCommandMap()->register("world", new Worlds(Main::getInstance()));
        Server::getInstance()->getCommandMap()->register("restart", new Restart(Main::getInstance()));
        Server::getInstance()->getCommandMap()->register("coord", new Coord(Main::getInstance()));
        Server::getInstance()->getCommandMap()->register("kick", new Kick(Main::getInstance()));
        Server::getInstance()->getCommandMap()->register("staff", new Staff(Main::getInstance()));
        Server::getInstance()->getCommandMap()->register("alias", new Alias(Main::getInstance()));
        Server::getInstance()->getCommandMap()->register("ban", new Ban(Main::getInstance()));
        Server::getInstance()->getCommandMap()->register("unban", new Unban(Main::getInstance()));
        Server::getInstance()->getCommandMap()->register("dm", new Dm(Main::getInstance()));
        Server::getInstance()->getCommandMap()->register("tp", new Tp(Main::getInstance()));
        Server::getInstance()->getCommandMap()->register("tpall", new Tpall(Main::getInstance()));
        Server::getInstance()->getCommandMap()->register("gm", new Gm(Main::getInstance()));
        Server::getInstance()->getCommandMap()->register("mute", new Mute(Main::getInstance()));
        Server::getInstance()->getCommandMap()->register("setgroup", new Setgroup(Main::getInstance()));
        Server::getInstance()->getCommandMap()->register("duel", new Duel(Main::getInstance()));
    }

}
