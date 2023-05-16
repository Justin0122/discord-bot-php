<?php

namespace Bot\Commands\Spotify;

use Bot\Builders\EmbedBuilder;
use Bot\Classes\SpotifyC;
use Bot\Classes\UserC;
use Bot\Helpers\SessionHandler;
use Bot\Helpers\TokenHandler;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use SpotifyWebAPI\SpotifyWebAPIException;
use Discord\Parts\Channel\Message;
use Discord\Parts\Interactions\Interaction;

class GeneratePlaylist
{

    public function getName(): string
    {
        return 'generateplaylist';
    }

    public function getDescription(): string
    {
        return 'Generate a playlist based on your liked songs. Max 250 songs.';
    }

    public function getGuildId(): ?string
    {
        return null;
    }

    public function getOptions(): array
    {
        //add optional argument for public or private playlist
        return [
            [
                'name' => 'public',
                'description' => 'Make the playlist public',
                'type' => 5,
                'required' => false
            ],
            [
                'name' => 'start_date',
                'description' => 'Start date for the playlist (default: 1 month ago)',
                'type' => 3,
                'required' => false
            ],
            [
                'name' => 'end_date',
                'description' => 'End date for the playlist (default: today)',
                'type' => 3,
                'required' => false
            ]
        ];
    }


    public function handle(Interaction $interaction, $discord): void
    {
        $user_id = $interaction->member->user->id;

        $tokenHandler = new TokenHandler($_ENV['API_URL'], $_ENV['SECURE_TOKEN']);
        $tokens = $tokenHandler->getTokens($user_id);

        if (!$tokens) {
            $embed = EmbedBuilder::create($discord)
                ->setFailed()
                ->setDescription('You need to authorize the bot first by using the `/spotify` command.');
            $interaction->respondWithMessage(
                MessageBuilder::new()->addEmbed($embed->build()), true
            );
        }
        $api = (new SessionHandler())->setSession($user_id);
        $me = $api->me();
        $pid = pcntl_fork();

        if ($pid > 0) {

            $optionRepository = $interaction->data->options;
            $startDate = $optionRepository['start_date'] ? new \DateTime($optionRepository['start_date']->value) : new \DateTime('-1 month');
            $playlistTitle = 'Liked Songs from ' . $startDate->format('M Y') .'.';

            if ($startDate > new \DateTime()) {
                $embed = EmbedBuilder::create($discord)
                    ->setFailed()
                    ->setDescription('Start date cannot be in the future.');
                $interaction->respondWithMessage(
                    MessageBuilder::new()->addEmbed($embed->build()), true
                );
                return;
            }

            $messageBuilder = MessageBuilder::new()
                ->addEmbed(
                    EmbedBuilder::create($discord)
                        ->setTitle('Generating playlist...')
                        ->setDescription('This may take a while.')
                        ->addField('User', $me->display_name)
                        ->addField('Playlist title', $playlistTitle)
                        ->setInfo()
                        ->build()
                );
            $interaction->respondWithMessage($messageBuilder, true, true);

        } else {
            try {
                $optionRepository = $interaction->data->options;
                $startDate = $optionRepository['start_date'] ? new \DateTime($optionRepository['start_date']->value) : new \DateTime('-1 month');
                $endDate = $optionRepository['end_date'] ? new \DateTime($optionRepository['end_date']->value) : (clone $startDate)->modify('+1 month');

                $playlistTitle = 'Liked Songs from ' . $startDate->format('M Y') .'.';
                $playlists = $api->getUserPlaylists($me->id);
                foreach ($playlists->items as $playlist) {
                    if ($playlist->name == $playlistTitle) {
                        $playlistExists = true;
                        $playlistId = $playlist->id;
                        break;
                    }
                }
                $totalTracks = 250; // Total number of tracks to fetch
                $limit = 50; // Number of tracks per request
                $offset = 0; // Initial offset


                $trackUris = []; // Array to store track URIs

                // Fetch tracks in batches until there are no more tracks available
                while (count($trackUris) < $totalTracks) {
                    $tracks = $api->getMySavedTracks([
                        'limit' => $limit,
                        'offset' => $offset,
                        'time_range' => 'short_term'
                    ]);
                    if (empty($tracks->items)) {
                        break;
                    }
                    $filteredTracks = SpotifyC::filterTracksByDate($tracks->items, $startDate, $endDate);
                    $trackUris = array_merge($trackUris, SpotifyC::extractTrackUris($filteredTracks));

                    $offset += $limit; // Increment the offset for the next request
                }

                if (empty($trackUris)) {
                    echo 'No tracks found.' . PHP_EOL;
                    exit(1); // Terminate the child process
                }

                $public = $optionRepository['public'] ? $optionRepository['public']->value : false;

                $playlistTitle = 'Liked Songs from ' . $startDate->format('M Y') .'.';
                $playlist = $api->createPlaylist([
                    'name' => $playlistTitle,
                    'public' => $public,
                    'description' =>
                        'This playlist was generated with your liked songs from ' .
                        $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d') . '.'
                ]);

                // Add tracks to the playlist in batches of 100
                $trackUris = array_chunk($trackUris, 100);
                foreach ($trackUris as $trackUri) {
                    $api->addPlaylistTracks($playlist->id, $trackUri);
                }

                echo 'Playlist generated: ' . $playlist->external_urls->spotify . "\n Title: " . $playlistTitle . PHP_EOL;
                exit(0);
            } catch (SpotifyWebAPIException $e) {
                // Terminate the child process
                echo $e->getMessage() . PHP_EOL;
                exit(1);
            }
        }

        pcntl_signal(SIGCHLD, function () use ($interaction, $discord) {
            // Wait for any child process to exit
            $pid = pcntl_waitpid(-1, $status, WNOHANG);

            // Check if the child process exited successfully (exit code 0)
            if ($pid > 0 && pcntl_wifexited($status) && pcntl_wexitstatus($status) === 0) {
                $this->updateEmbed($interaction, $discord);
            }
            else{

                if(pcntl_wexitstatus($status) === 1){
                    $errorStatus = 'No tracks found.';
                }
                else{
                    $errorStatus = 'Unexpected error. Please try again later.';
                }

                $interaction->updateOriginalResponse(MessageBuilder::new()
                    ->addEmbed(
                        EmbedBuilder::create($discord)
                            ->setTitle('Error')
                            ->setDescription('An error occurred while generating the playlist.')
                            ->AddField('Error', 'code: ' . pcntl_wexitstatus($status) . PHP_EOL . $errorStatus)
                            ->setFailed()
                            ->build()
                    )
                );
            }
        });
    }


    public function updateEmbed(Interaction $interaction, $discord): void
    {
        $user_id = UserC::getInteractionUserID($interaction);
        $api = (new SessionHandler())->setSession($user_id);

        $me = SpotifyC::getMe($api);

        $playlist = SpotifyC::getFirstPlaylist($api, $me->id);

        $playlistUrl = 'https://open.spotify.com/playlist/' . $playlist->id;

        $responseBuilder = $interaction->updateOriginalResponse(MessageBuilder::new()->addEmbed(
                EmbedBuilder::create($discord)
                    ->setTitle('Playlist generated!')
                    ->setDescription('Your playlist ' . $playlist->name . ' has been generated.')
                    ->addField('Title', $playlist->name)
                    ->addField('User', $me->display_name)
                    ->addField('Playlist', $playlistUrl)
                    ->setSuccess()
                    ->build()
            )
        );
    }

}