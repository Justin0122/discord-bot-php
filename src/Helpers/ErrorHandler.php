<?php

namespace Bot\Helpers;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Builders\MessageBuilder;
use Bot\Builders\EmbedBuilder;
use Discord\Parts\Embed\Embed;

class ErrorHandler
{
    public static function handle(string $message): Embed
    {
        return EmbedBuilder::create(new Discord())
            ->setTitle('Error')
            ->setDescription($message)
            ->setFailed()
            ->build();
    }

    public static function handleException($e): Embed
    {
        return EmbedBuilder::create(new Discord())
            ->setTitle('Error')
            ->setDescription($e->getMessage())
            ->setFailed()
            ->build();
    }
}