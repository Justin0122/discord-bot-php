<?php

namespace Bot\Commands\Spotify;

use Bot\Helpers\ErrorHandler;
use Bot\Helpers\SessionHandler;
use Bot\Helpers\TokenHandler;
use bot\Scheduler\PlaylistScheduler;

class GeneratePlaylist
{
    public function getName(): string
    {
        return 'generateplaylist';
    }
    public function getDescription(): string
    {
        return 'Generate a playlist based on your liked songs';
    }
    public function getOptions(): array
    {
        return [];
    }

    public function handle($args, $discord, $username, $user_id): array
    {
        $tokenHandler = new TokenHandler($_ENV['API_URL'], $_ENV['SECURE_TOKEN']);
        $tokens = $tokenHandler->getTokens($user_id);

        if (!$tokens) {
            return ErrorHandler::handle("Please register using the `spotify` command first.");
        }

        //set the api using sessionHandler
        $api = (new SessionHandler())->setSession($user_id);

        $me = $api->me();

        return [];

    }

}