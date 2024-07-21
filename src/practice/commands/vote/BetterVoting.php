<?php

namespace practice\commands\vote;

use practice\Main;
use pocketmine\command\Command;
use pocketmine\command\PluginCommand;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use practice\provider\WorkerProvider;

class BetterVoting extends PluginCommand
{

    /** @var null|string $apiKey */
    private $apiKey = "LeCxBuv2gyBtj4kruANz5SWzcDCM5atly";
    /** @var array $data */
    private $data = [];
    /** @var string[] $processing */
    private $processing = [];

    public function __construct($plugin)
    {
        parent::__construct("vote", $plugin);
        $this->setDescription("Vote command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        $sender->sendMessage("§a» Checking your vote...");
        if (empty($args[0])) {
            if (!$sender instanceof Player) {
                $sender->sendMessage(TextFormat::RED . "Use '/vote reload', or use command in game");
                return false;
            }
            if ($this->apiKey === null) {
                $sender->sendMessage(TextFormat::RED . "This server has not provided a valid API key in their configuration");
                return false;
            }
            if (in_array($sender->getName(), $this->processing)) {
                $sender->sendMessage(TextFormat::RED . "§cVote in progress...");
                return false;
            }
            $this->processing[spl_object_id($sender)] = $sender;
            Main::getInstance()->getServer()->getAsyncPool()->submitTaskToWorker(new ProcessVoteTask("LeCxBuv2gyBtj4kruANz5SWzcDCM5atly", $sender->getName()), WorkerProvider::COMMAND_ASYNC);
            return true;
        }
        switch ($args[0]) {
            case "reload":
                if (!$sender->hasPermission("bettervoting.reload")) {
                    $sender->sendMessage(TextFormat::RED . "You do not have permission to use this command");
                    break;
                }
                $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
                if (empty($config->get("api-key"))) {
                    $this->getLogger()->error("Please give a valid API key in " . $this->getDataFolder() . "config.yml");
                    $sender->sendMessage("Please give a valid API key in " . $this->getDataFolder() . "config.yml");
                } else $this->apiKey = $config->get("api-key");
                if (!is_array($config->get("claim"))) {
                    $this->getLogger()->error("Please give a valid configuration in " . $this->getDataFolder() . "config.yml (Delete to reset)");
                    $sender->sendMessage("Please give a valid configuration in " . $this->getDataFolder() . "config.yml (Delete to reset)");
                } else $this->data = $config->get("claim");
                $sender->sendMessage(TextFormat::GREEN . "Configuration successfully reloaded");
                break;
        }
        return true;
    }

    public function translateMessage(string $message, Player $player): string
    {
        return str_replace([
            "{real-name}",
            "{display-name}",
            "&",
            "{x}",
            "{floor-x}",
            "{y}",
            "{floor-y}",
            "{z}",
            "{floor-z}",
        ], [
            $player->getName(),
            $player->getDisplayName(),
            "§",
            $player->getX(),
            $player->getFloorX(),
            $player->getY(),
            $player->getFloorY(),
            $player->getZ(),
            $player->getFloorZ()
        ], $message);
    }

    public function claimVote(Player $player): void
    {
        $data = $this->data;
        if (isset($data["broadcast"])) $player->getServer()->broadcastMessage($this->translateMessage($data["broadcast"], $player));
        if (isset($data["message"])) $player->sendMessage($this->translateMessage($data["message"], $player));
        foreach ($this->getItemRewards() as $reward) {
            if ($player->getInventory()->canAddItem($reward)) $player->getInventory()->addItem($reward);
            else $player->getLevel()->dropItem($player, $reward);
        }
        if (isset($data["commands"]) && is_array($data["commands"])) foreach ($data["commands"] as $command) $this->getServer()->dispatchCommand(new ConsoleCommandSender(), $this->translateMessage($command, $player));
    }

    public function stopProcessing(Player $player): void
    {
        unset($this->processing[spl_object_id($player)]);
    }
}
