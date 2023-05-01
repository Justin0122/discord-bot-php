<?php

include __DIR__.'/vendor/autoload.php';

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Intents;
use Discord\WebSockets\Event;

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$discord = new Discord([
    'token' => $_ENV['DISCORD_BOT_TOKEN'],
    'intents' => Intents::getDefaultIntents()
]);

$discord->on('ready', function (Discord $discord) {
    echo "Bot is ready!", PHP_EOL;

    $channel = $discord->getChannel($_ENV['CHANNEL_ID']);

    $discord->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) use ($channel) {
        if ($message->author->id != $discord->id) {
            $channel->sendMessage("{$message->author->username} said: {$message->content}");
        }
    });

    ob_start(function ($buffer) use ($channel) {
        $channel->sendMessage($buffer);
        return $buffer;
    });
});


$discord->run();