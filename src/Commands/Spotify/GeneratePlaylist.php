<?php

namespace Bot\Commands\Spotify;

use Bot\Builders\EmbedBuilder;
use Bot\Classes\SpotifyC;
use Bot\Classes\UserC;
use Bot\Helpers\ErrorHandler;
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
            $interaction->respondWithMessage(
                MessageBuilder::new()->addEmbed(ErrorHandler::handle("You need to authorize the bot first by using the '/spotify' command."))
            );
        }

        // Set the API using SessionHandler
        $api = (new SessionHandler())->setSession($user_id);

        $me = $api->me();

        $pid = pcntl_fork();

        if ($pid > 0) {
            // Parent process
            // ...

            $optionRepository = $interaction->data->options;
            $startDate = $optionRepository['start_date'] ? new \DateTime($optionRepository['start_date']->value) : new \DateTime('-1 month');
            $playlistTitle = 'Liked Songs from ' . $startDate->format('M Y') .'.';

            // Create a MessageBuilder and set the initial embed content
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

            // Send the initial message with the embed
            $interaction->respondWithMessage($messageBuilder, true, true);

        } else {
            // Child process
            try {
                $optionRepository = $interaction->data->options;
                $startDate = $optionRepository['start_date'] ? new \DateTime($optionRepository['start_date']->value) : new \DateTime('-1 month');
                $endDate = $optionRepository['end_date'] ? new \DateTime($optionRepository['end_date']->value) : (clone $startDate)->modify('+1 month');

                $playlistTitle = 'Liked Songs from ' . $startDate->format('M Y') .'.';
                //check if playlist already exists
                $playlists = $api->getUserPlaylists($me->id);
                $playlistExists = false;
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

                    // Check if there are no more tracks available
                    if (empty($tracks->items)) {
                        break;
                    }

                    $filteredTracks = array_filter($tracks->items, function ($item) use ($startDate, $endDate) {
                        $addedAt = new \DateTime($item->added_at);
                        return $addedAt >= $startDate && $addedAt <= $endDate;
                    });

                    // Extract track URIs from the filtered tracks
                    $trackUris = array_merge($trackUris, array_map(function ($item) {
                        return $item->track->uri;
                    }, $filteredTracks));

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

                // Terminate the child process
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
                $interaction->updateOriginalResponse(MessageBuilder::new()
                    ->addEmbed(
                        EmbedBuilder::create($discord)
                            ->setTitle('Error')
                            ->setDescription('An error occurred while generating the playlist.')
                            ->AddField('Error', 'Error code: ' . pcntl_wexitstatus($status))
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

        $responseBuilder = $interaction->updateOriginalResponse(MessageBuilder::new()
            ->addEmbed(
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