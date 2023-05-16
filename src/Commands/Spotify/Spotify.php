<?php

namespace Bot\Commands\Spotify;

use Bot\Builders\EmbedBuilder;
use Bot\Helpers\SessionHandler;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;
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

    public function getGuildId(): ?string
    {
        return null;
    }

    public function getOptions(): array
    {
        return [];
    }

    public function handle(Interaction $interaction, $discord): void
    {
        $user_id = $interaction->member->user->id;
        $api = (new SessionHandler())->setSession($user_id);
        $me = $api->me();

        if ($me) {
            $embed = EmbedBuilder::create($discord)
                ->setSuccess()
                ->setTitle('Spotify')
                ->setDescription('You are already authorized');
            $interaction->respondWithMessage(
                MessageBuilder::new()->addEmbed($embed->build()), true
            );
            return;
        }

        // create a new session instance
        $session = new Session(
            $_ENV['SPOTIFY_CLIENT_ID'],
            $_ENV['SPOTIFY_CLIENT_SECRET'],
            $_ENV['SPOTIFY_REDIRECT_URI']
        );

        $user_id = $interaction->member->user->id;
        $url = "https://accounts.spotify.com/authorize?client_id={$_ENV['SPOTIFY_CLIENT_ID']}&response_type=code&redirect_uri={$_ENV['SPOTIFY_REDIRECT_URI']}&scope=user-read-email%20user-read-private%20user-library-read%20user-top-read%20user-read-recently-played%20user-read-playback-state%20user-read-currently-playing%20user-follow-read%20user-read-playback-position%20playlist-read-private%20playlist-modify-public%20playlist-modify-private%20playlist-read-collaborative%20user-library-modify%20user-follow-modify%20user-modify-playback-state%20user-read-recently-played%20user-read-playback-state%20user-modify-playback-state%20user-read-currently-playing%20user-read-playback-position%20user-read-recently-played%20user-read-playback-state%20user-modify-playback-state%20user-read-currently-playing%20user-read-playback-position%20user-read-recently-played%20user-read-playback-state%20user-modify-playback-state%20user-read-currently-playing%20user-read-playback-position%20user-read-recently-played%20user-read-playback-state%20user-modify-playback-state&state={$user_id}";

        $embed = EmbedBuilder::create($discord)
            ->setTitle('Authorize Spotify')
            ->setDescription("Click [here]({$url}) to authorize the bot to access your Spotify account.")
            ->setSuccess()
            ->build();

        $interaction->respondWithMessage(
            MessageBuilder::new()->addEmbed($embed), true
        );
    }
}