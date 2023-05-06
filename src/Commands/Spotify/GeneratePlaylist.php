<?php

namespace Bot\Commands\Spotify;

use Bot\Helpers\ErrorHandler;
use Bot\Helpers\SessionHandler;
use Bot\Helpers\TokenHandler;
use SpotifyWebAPI\SpotifyWebAPIException;

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
        //add optional argument for public or private playlist
        return [
            [
                'name' => 'public',
                'description' => 'Make the playlist public',
                'type' => 5,
                'required' => false
            ]
        ];
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

        try {
            $totalTracks = 100; // Total number of tracks to fetch
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
                $endDate = new \DateTime();
                $startDate = (clone $endDate)->modify('-1 month');

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
                return ErrorHandler::handle("No tracks found within the last month.");
            }

            // Generate playlist title
            $playlistTitle = $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d') . ' - Generated for ' . $me->display_name;

            // Create the playlist
            $playlist = $api->createPlaylist([
                'name' => $playlistTitle,
                'public' => $args['public'] ?? false
            ]);

            // Make the playlist with the tracks
            $api->addPlaylistTracks($playlist->id, $trackUris);

            return [
                'title' => 'Playlist generated!',
                'content' => 'Playlist generated! Click [here](https://open.spotify.com/playlist/' . $playlist->id . ') to view it.',
                'color' => hexdec('00FF00'),
                'fields' => [
                    [
                        'name' => 'Playlist Title',
                        'value' => $playlistTitle,
                        'inline' => false
                    ],
                    [
                        'name' => 'Tracks',
                        'value' => count($trackUris),
                        'inline' => false
                    ]
                ]
            ];
        } catch (SpotifyWebAPIException $e) {
            return ErrorHandler::handleException($e);
        }
    }
}