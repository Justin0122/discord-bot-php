<?php

namespace Bot\Scheduler;

use Bot\Helpers\ErrorHandler;
use SpotifyWebAPI\SpotifyWebAPI;

class PlaylistScheduler
{
    public function getPlaylistUsers()
    {

    }

    public function generatePlaylist($user): array
    {
        $accessToken = $user['access_token'];
        $api = new SpotifyWebAPI();
        $api->setAccessToken($accessToken);

        try {
            $me = $api->me();
            echo "Got user info from Spotify: " . $me->display_name . "\n";
        } catch (\Exception $e) {
            echo "Failed to get user info from Spotify: " . $e->getMessage() . "\n";
        }

        $tracks = $api->getMySavedTracks([
            'limit' => 10
        ]);

        $firstSong = $tracks->items[0]->added_at;
        $lastSong = $tracks->items[count($tracks->items) - 1]->added_at;

        $playlistName = $firstSong . ' - ' . $lastSong;

        $api->createPlaylist($me->id, [
            'name' => $playlistName
        ]);

        $playlist = $api->getUserPlaylists($me->id, [
            'limit' => 1
        ]);

        $playlistId = $playlist->items[0]->id;

        $songUris = [];

        foreach ($tracks->items as $item) {
            $songUris[] = $item->track->uri;
        }

        $api->addPlaylistTracks($playlistId, $songUris);
        //echo to the console that the playlist was generated
        echo "Generated playlist for " . $me->display_name . "\n";
        return[];
    }

}