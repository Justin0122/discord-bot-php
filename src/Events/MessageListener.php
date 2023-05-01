<?php

namespace Bot\Events;

use Discord\Discord;
use Discord\Parts\Channel\Message;


class MessageListener
{
    public function handle(Message $message, Discord $discord, $channel): void
    {
        if ($message->author->id != $discord->id) {
            $channel->sendMessage("{$message->author->username} said: {$message->content}");
        }
    }
}
