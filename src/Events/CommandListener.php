<?php

namespace Bot\Events;

use Bot\Helpers\CommandHandler;
use Bot\Helpers\TokenHandler;
use Discord\Discord;
use Discord\Http\Exceptions\NoPermissionsException;
use Discord\Parts\Channel\Message;


class CommandListener
{
    public function handle($interaction, Discord $discord): void
    {
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
    }
}
