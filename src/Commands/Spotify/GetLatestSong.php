<?php

namespace Bot\Commands\Spotify;

use Bot\Helpers\ErrorHandler;
use GuzzleHttp\Client;
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

        $api_url = $_ENV['API_URL'];
        $secure_token = $_ENV['SECURE_TOKEN'];
        $discord_id = $user_id;

        $link = "$api_url$discord_id?secure_token=$secure_token&discord_id=$discord_id";

        $client = new Client();

        try{
        $response = $client->request('GET', $link);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return ErrorHandler::handle("Please register using the `spotify` command first.");
        }
        $response = json_decode($response->getBody(), true);

        $users = $response['data']['attributes'];
        $discord_id = $users['discord_id'];
        $accessToken = $users['spotify_access_token'];

        $api = new SpotifyWebAPI();
        $api->setAccessToken($accessToken);

        try {
            $me = $api->me();
        } catch (\SpotifyWebAPI\SpotifyWebAPIException $e) {
            if ($e->getMessage() == 'The access token expired') {
                $session = new Session(
                    $_ENV['SPOTIFY_CLIENT_ID'],
                    $_ENV['SPOTIFY_CLIENT_SECRET'],
                    $_ENV['SPOTIFY_REDIRECT_URI']
                );
                $session->refreshAccessToken($users['spotify_refresh_token']);
                $accessToken = $session->getAccessToken();
                $refreshToken = $session->getRefreshToken();
                $link = "$api_url?discord_id=$discord_id&secure_token=$secure_token&spotify_access_token=$accessToken&spotify_refresh_token=$refreshToken";
                $client->request('GET', $link);

                $api->setAccessToken($accessToken);
                $me = $api->me();
                echo 'Access token refreshed for ' . $me->display_name . PHP_EOL;
            } else {
                return ErrorHandler::handle($e, $discord);
            }
        }

        $tracks = $api->getMySavedTracks([
            'limit' => 10
        ]);

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