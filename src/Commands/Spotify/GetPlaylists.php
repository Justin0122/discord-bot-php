<?php

namespace Bot\Commands\Spotify;

use Bot\Helpers\ErrorHandler;
use Bot\Helpers\SessionHandler;
use Bot\Helpers\TokenHandler;
use Discord\Discord;
use SpotifyWebAPI\SpotifyWebAPIException;
use Discord\Parts\Channel\Message;

class GetPlaylists
{

    public function getName(): string
    {
        return 'getplaylists';
    }

    public function getDescription(): string
    {
        return 'Get a list of your playlists.';
    }

    public function getGuildId(): ?string
    {
        return null;
    }

    public function getOptions(): array
    {
        return [];
    }


    public function handle($args, Discord $discord, $username, $user_id): array
    {
        $tokenHandler = new TokenHandler($_ENV['API_URL'], $_ENV['SECURE_TOKEN']);
        $tokens = $tokenHandler->getTokens($user_id);

        if (!$tokens) {
            return ErrorHandler::handle("Please register using the `spotify` command first.");
        }

        // Set the API using SessionHandler
        $api = (new SessionHandler())->setSession($user_id);

        $me = $api->me();


        $playlists = $api->getUserPlaylists($me->id, [
            'limit' => 50
        ]);

        $response = [
            'title' => 'Your playlists',
            'content' => '',
            'fields' => []
        ];

        foreach ($playlists->items as $playlist) {
            $response['fields'][] = [
                'name' => $playlist->name,
                'value' => $playlist->external_urls->spotify
            ];
        }

        return $response;

    }
}