<?php

namespace Bot\Commands\Spotify;

use Bot\Helpers\ErrorHandler;
use Bot\Helpers\SessionHandler;
use Bot\Helpers\TokenHandler;
use Discord\Discord;
use SpotifyWebAPI\SpotifyWebAPIException;
use Discord\Parts\Channel\Message;

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

        // Create a new child process to handle the playlist generation
        $pid = pcntl_fork();

        if ($pid == -1) {
            // Fork failed, handle the error
            return ErrorHandler::handle("Failed to create a background process for playlist generation.");
        } elseif ($pid > 0) {
            // Parent process
            // Return an embed indicating that the playlist is being generated
            $startDate = $args['start_date'] ? new \DateTime($args['start_date']) : (new \DateTime())->modify('-1 month');
            $endDate = $args['end_date'] ? new \DateTime($args['end_date']) : ($args['start_date'] ? (new \DateTime($args['start_date']))->modify('+1 month') : new \DateTime());
            $playlistTitle = 'Liked Songs from ' . $startDate->format('M Y') .'.';

            $playlists = $api->getUserPlaylists($me->id);
            foreach ($playlists->items as $playlist) {
                if ($playlist->name == $playlistTitle) {
                    return ErrorHandler::handle("A playlist with the name `$playlistTitle` already exists. Please delete it and try again.");
                }
            }

            if ($startDate > $endDate) {
                return ErrorHandler::handle("Start date cannot be after end date.");
            }

            if ($endDate > new \DateTime()) {
                return ErrorHandler::handle("End date cannot be in the future.");
            }


            return [
                'title' => 'Playlist Generation',
                'content' => 'Your playlist is being generated. Click [here](https://open.spotify.com/user/' . $me->id . ') to view your Spotify profile.',
                'color' => hexdec('FFFF00'),
                'fields' => [
                    [
                        'name' => 'Playlist Name',
                        'value' => $playlistTitle,
                        'inline' => false
                    ],
                    [
                        'name' => 'Playlist Description',
                        'value' => 'This playlist was generated with your liked songs from ' .
                            $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d') . '.',
                        'inline' => false
                    ],
                    [
                        'name' => 'Playlist Owner',
                        'value' => $me->display_name,
                        'inline' => true
                    ]
                ]
            ];
        } else {
            // Child process
            try {
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

                    // Filter tracks added within the last month
                    $startDate = $args['start_date'] ? new \DateTime($args['start_date']) : (new \DateTime())->modify('-1 month');
                    $endDate = isset($args['end_date'])
                        ? new \DateTime($args['end_date'])
                        : ($args['start_date'] ? (new \DateTime($args['start_date']))->modify('+1 month') : new \DateTime());
                    $startYear = $startDate->format('Y');

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
                    exit(); // Terminate the child process
                }


                $playlistTitle = 'Liked Songs from ' . $startDate->format('M Y') .'.';
                $playlist = $api->createPlaylist([
                    'name' => $playlistTitle,
                    'public' => $args['public'] ?? false,
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
                exit();
            } catch (SpotifyWebAPIException $e) {
                // Terminate the child process
                echo $e->getMessage() . PHP_EOL;
                exit();
            }
        }
    }
}