<?php

include __DIR__.'/vendor/autoload.php';
include __DIR__ . '/Includes.php';

use Bot\Helpers\CommandHandler;
use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Intents;
use Discord\WebSockets\Event;
use Bot\Events\MessageListener;
use Bot\Helpers\CommandRegistrar;


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

    CommandRegistrar::register();

    $listener = new MessageListener();
    $discord->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) use ($channel, $listener) {
        $listener->handle($message, $discord, $channel);
    });

    $discord->on(Event::INTERACTION_CREATE, function ($interaction, Discord $discord) use ($channel) {
        $command = $interaction->data->name;
        $commandHandler = new CommandHandler();
        $commandHandler->runCommand($command, $channel);
        //acknowledge interaction
        $commandHandler = new CommandHandler();
        $response = $commandHandler->runCommand($command, $channel);
        $discord->getHttpClient()->post("/interactions/{$interaction->id}/{$interaction->token}/callback", [
            'type' => 4,
            'data' => [
                'content' => $response
            ]
        ]);


    });


    ob_start(function ($buffer) use ($channel) {
        $channel->sendMessage($buffer);
        return $buffer;
    });
});


$discord->run();