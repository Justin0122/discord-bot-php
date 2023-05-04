<?php

namespace Bot\Commands\Spotify;

use Bot\Helpers\ErrorHandler;
use SpotifyWebAPI\SpotifyWebAPI;
use SpotifyWebAPI\Session;

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

    public function getOptions(): array
    {
        return [];
    }

    public function handle($args, $discord, $username, $user_id): array
    {
        $users = json_decode(file_get_contents(__DIR__ . '/../../../users.json'), true);

        if (!isset($users[$user_id]['access_token'])) {
            return ErrorHandler::handle('User is not authenticated with Spotify');
        }

        $accessToken = $users[$user_id]['access_token'];
        $api = new SpotifyWebAPI();
        $api->setAccessToken($accessToken);

        try {
            $me = $api->me();
        } catch (\Exception $e) {
            return ErrorHandler::handle('Failed to get user info from Spotify: ' . $e->getMessage());
        }

        $tracks = $api->getMySavedTracks([
            'limit' => 10
        ]);

        $embed = [
            'title' => 'Latest songs added by '.$me->display_name,
            'description' => 'Here are the latest songs added by '.$me->display_name,
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