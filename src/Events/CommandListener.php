<?php

namespace Bot\Events;

use Discord\Discord;
use Discord\Parts\Channel\Message;

//listen for slash commands and handle them
class CommandListener
{
    public function handle(Message $message, Discord $discord, $channel): void
    {
        echo "CommandListener::handle() called", PHP_EOL;
    }
}