<?php

namespace Bot\Events;

use Discord\Discord;
use Discord\Http\Exceptions\NoPermissionsException;
use Discord\Parts\Channel\Message;


class MessageListener
{
    /**
     * @throws NoPermissionsException
     */
    public function handle(Message $message, Discord $discord): void
    {
        $channel = $discord->getChannel($message->channel_id);
        if ($message->author->id != $discord->id) {
            if ($message->author->id == "1103661771316805663") {
                $channel->sendMessage("Received Tokens: " . $message->content);
            }
        }
    }
}
