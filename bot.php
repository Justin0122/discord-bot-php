<?php

include __DIR__.'/vendor/autoload.php';
include __DIR__ . '/Includes.php';


use Bot\Helpers\RemoveAllCommands;
use Discord\Discord;
use Discord\Parts\Interactions\Interaction;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Intents;
use Discord\WebSockets\Event;
use Bot\Events\MessageListener;
use Bot\Helpers\CommandRegistrar;
use Discord\Builders\MessageBuilder;

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $discord = new Discord([
        'token' => $_ENV['DISCORD_BOT_TOKEN'],
        'intents' => Intents::getDefaultIntents()
    ]);
} catch (\Discord\Exceptions\IntentException $e) {
    echo $e->getMessage();
}


$discord->on('ready', function (Discord $discord) {
    echo "Bot is ready!", PHP_EOL;

//    RemoveAllCommands::removeAllCommands($discord);

    CommandRegistrar::register();

    $listener = new MessageListener();
    $discord->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) use ($listener) {
        $listener->handle($message, $discord);
    });

    //when a slash command is used, go to the command's handle function
    $discord->on(Event::INTERACTION_CREATE, function (Interaction $interaction, Discord $discord) {
        $command = CommandRegistrar::getCommandByName($interaction->data->name);
        if ($command) {
            //call the handle function and send the arguments and discord instance
            $command->handle($interaction, $discord);
        }
    });
});

$discord->run();