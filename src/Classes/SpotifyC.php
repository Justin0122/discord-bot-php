<?php

namespace Bot\Classes;

use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;

class SpotifyC
{

    public static function getPlaylists($api, $user_id)
    {
        $playlists = $api->getUserPlaylists($user_id, [
            'limit' => 50
        ]);

        $allPlaylists = $playlists->items;

        while ($playlists->next) {
            $playlists = $api->getNext($playlists);
            $allPlaylists = array_merge($allPlaylists, $playlists->items);
        }

        return $allPlaylists;
    }


    public static function getFirstPlaylist($api, $user_id)
    {
        $playlist = $api->getUserPlaylists($user_id, [
            'limit' => 1
        ]);

        $playlist = $playlist->items;

        return $playlist[0];
    }

    public static function getTracks($api, $playlist_id)
    {
        $tracks = $api->getPlaylistTracks($playlist_id, [
            'limit' => 100
        ]);

        $tracks = $tracks->items;

        while ($tracks->next) {
            $tracks = array_merge($tracks, $tracks->next());
        }

        return $tracks;
    }

    public static function getLikedSongs($api)
    {
        $tracks = $api->getMySavedTracks([
            'limit' => 50
        ]);

        $tracks = $tracks->items;

        while ($tracks->next) {
            $tracks = array_merge($tracks, $tracks->next());
        }

        return $tracks;
    }

    public static function getTrackURI($api, $track_id)
    {
        $track = $api->getTrack($track_id);

        return $track->uri;
    }

    public static function filterTracksByDate($tracks, $startDate, $endDate): array
    {
        return array_filter($tracks, function ($item) use ($startDate, $endDate) {
            $addedAt = new \DateTime($item->added_at);
            return $addedAt >= $startDate && $addedAt <= $endDate;
        });
    }

    public static function extractTrackURIs($tracks): array
    {
        return array_map(function ($item) {
            return $item->track->uri;
        }, $tracks);
    }

    public static function getMe($api)
    {
        return $api->me();
    }
}