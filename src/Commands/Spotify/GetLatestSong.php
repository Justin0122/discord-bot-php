<?php

namespace Bot\Commands\Spotify;

use Bot\Helpers\ErrorHandler;
use Bot\Helpers\SessionHandler;
use Bot\Helpers\TokenHandler;

class GetLatestSong
{
    public function getName(): string
    {
        return 'getlatestsong';
    }

    public function getDescription(): string
    {
        return 'Get the latest song from your liked songs';
    }

    public function getGuildId(): ?string
    {
        return null;
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

        //get the latest 10 songs
        $tracks = $api->getMySavedTracks([
            'limit' => 10
        ]);

        $me = $api->me();
        $embed = [
            'title' => 'Latest songs added by ' . $me->display_name,
            'content' => '',
            'color' => hexdec('34ebd8'),
            'fields' => []
        ];

        foreach ($tracks->items as $item) {
            $track = $item->track;
            $embed['fields'][] = [
                'name' => $track->name,
                'value' => 'By: ' . $track->artists[0]->name . ' | Album: ' . $track->album->name . ' | Added: ' . $item->added_at,
                'inline' => false,
            ];
        }
        return $embed;
    }

}