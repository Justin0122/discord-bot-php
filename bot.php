<?php

include __DIR__.'/vendor/autoload.php';
include __DIR__ . '/Includes.php';

use Bot\Helpers\CommandHandler;
use Bot\Helpers\ImageHelper;
use Bot\Helpers\RemoveAllCommands;
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

//    RemoveAllCommands::removeAllCommands($discord);

    CommandRegistrar::register();

    $listener = new MessageListener();
    $discord->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) use ($listener) {
        $listener->handle($message, $discord);
    });

    $discord->on(Event::INTERACTION_CREATE, function ($interaction, Discord $discord) {
        $command = $interaction->data->name;
        $options = $interaction->data->options ?? [];
        $args = [];
        foreach ($options as $option) {
            $args[$option->name] = $option->value;
        }
        $commandHandler = new CommandHandler();
        $username = $interaction->member->user->username;
        $user_id = $interaction->member->user->id;
        $response = $commandHandler->runCommand($command, $args, $discord, $username, $user_id);

        $embed = [
            'title' => $response['title'] ?? '',
            'color' => $response['color'] ?? hexdec('00FF00'),
            'description' => $response['content'] ?? '',
            'fields' => $response['fields'] ?? [],
            'footer' => $response['footer'] ?? [],
            'thumbnail' => $response['thumbnail'] ?? [],
            'image' => $response['image'] ?? [],
        ];

        $data = [
            'embeds' => [$embed],
            'flags' => $response['flags'] ?? 0,
            'file' => $response['file'] ?? ''
        ];

        $discord->getHttpClient()->post("/interactions/{$interaction->id}/{$interaction->token}/callback", [
            'type' => 4,
            'data' => $data
        ]);

        if (isset($response['file'])) {
            $interaction->channel->sendFile($response['file']);
        }
    });

});


$discord->run();