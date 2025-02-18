<?php

namespace practice\api\discord;

use practice\api\discord\task\DiscordWebhookSendTask;
use pocketmine\Server;

class Webhook
{
    /** @var string */
    protected $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function getURL(): string
    {
        return $this->url;
    }

    public function isValid(): bool
    {
        return filter_var($this->url, FILTER_VALIDATE_URL) !== false;
    }

    public function send(Message $message): void
    {
        Server::getInstance()->getAsyncPool()->submitTask(new DiscordWebhookSendTask($this, $message));
    }
}
