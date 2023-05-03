<?php

namespace Bot\Commands\Spotify;

use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;


class Spotify
{
    public function getName(): string
    {
        return 'spotify';
    }
    public function getDescription(): string
    {
        return 'Allow the bot to access your spotify account';
    }
    public function getOptions(): array
    {
        return [];
    }

    public function handle($args, $discord, $username, $user_id)
    {
        // create a new session instance
        $session = new Session(
            $_ENV['SPOTIFY_CLIENT_ID'],
            $_ENV['SPOTIFY_CLIENT_SECRET'],
            $_ENV['SPOTIFY_REDIRECT_URI']
        );

        // request authorization
        $options = [
            'scope' => [
                'user-read-email',
                'user-read-private',
                'user-library-read',
                'user-top-read',
                'user-read-recently-played',
                'user-read-playback-state',
                'user-read-currently-playing',
                'user-follow-read',
                'user-read-playback-position',
                'user-read-recently-played',
                'user-read-playback-state',
                'user-modify-playback-state',
                'user-read-currently-playing',
                'user-read-playback-position',
                'user-read-recently-played',
                'user-read-playback-state',
                'user-modify-playback-state',
                'user-read-currently-playing',
                'user-read-playback-position',
                'user-read-recently-played',
                'user-read-playback-state',
                'user-modify-playback-state',
                'user-read-currently-playing',
                'user-read-playback-position',
                'user-read-recently-played',
                'user-read-playback-state',
                'user-modify-playback-state',
                'user-read-currently-playing',
                'user-read-playback-position',
                'user-read-recently-played',
                'user-read-playback-state',
                'user-modify-playback-state',
                'user-read-currently-playing',
                'user-read-playback-position',
                'user-read-recently-played',
                'user-read-playback-state',
                'user-modify-playback-state',
                'user-read-currently-playing',
                'user-read-playback-position',
                'user-read-recently-played',
                'user-read-playback-state',
                'user-modify-playback-state',
            ],
        ];

        $session->requestCredentialsToken($options['scope']);
        $accessToken = $session->getAccessToken();
        $refreshToken = $session->getRefreshToken();
        $api = new SpotifyWebAPI();
        $api->setAccessToken($accessToken);


        $users = json_decode(file_get_contents('users.json'), true);
        $users[$user_id] = [
            'access_token' => $accessToken,
            'playlist_gen' => false,
        ];
        file_put_contents('users.json', json_encode($users));



        $url = "https://accounts.spotify.com/authorize?client_id={$_ENV['SPOTIFY_CLIENT_ID']}&response_type=code&redirect_uri={$_ENV['SPOTIFY_REDIRECT_URI']}&scope=user-read-email%20user-read-private%20user-library-read%20user-top-read%20user-read-recently-played%20user-read-playback-state%20user-read-currently-playing%20user-follow-read%20user-read-playback-position%20user-read-recently-played%20user-read-playback-state%20user-modify-playback-state%20user-read-currently-playing%20user-read-playback-position%20user-read-recently-played%20user-read-playback-state%20user-modify-playback-state%20user-read-currently-playing%20user-read-playback-position%20user-read-recently-played%20user-read-playback-state%20user-modify-playback-state%20user-read-currently-playing%20user-read-playback-position%20user-read-recently-played%20user-read-playback-state%20user-modify-playback-state%20user-read-currently-playing%20user-read-playback-position%20user-read-recently-played%20user-read-playback-state%20user-modify-playback-state";
        return [
            'title' => 'Spotify Login for '.$username,
            'content' => "Click [here]($url) to login to spotify",
            'flags' => 64,
            'color' => hexdec('34ebd8')
        ];
    }
}