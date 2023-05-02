<?php

require 'vendor/autoload.php';

use Dotenv\Dotenv;
use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$session = new Session(
    $_ENV['SPOTIFY_CLIENT_ID'],
    $_ENV['SPOTIFY_CLIENT_SECRET'],
    $_ENV['SPOTIFY_REDIRECT_URI']
);

// Request a token using the code from Spotify
$session->requestAccessToken($_GET['code']);
$accessToken = $session->getAccessToken();

file_put_contents(__DIR__ . '/access_token.txt', $accessToken);

$api = new SpotifyWebAPI();
$api->setAccessToken($accessToken);

$user = $api->me();

echo "<script>window.close();</script>";