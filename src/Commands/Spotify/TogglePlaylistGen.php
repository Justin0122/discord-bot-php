<?php

namespace Bot\Commands\Spotify;

class TogglePlaylistGen
{
    public function getName(): string
    {
        return 'toggleplaylistgen';
    }
    public function getDescription(): string
    {
        return 'Toggle the playlist generator';
    }
    public function getOptions(): array
    {
        return [];
    }

    public function handle($args, $discord, $username, $user_id)
    {
        //get the current state of the playlist generator from the users.json file where the user_id is the key
        $users = json_decode(file_get_contents(__DIR__.'/../../../users.json'), true);

        if (!isset($users[$user_id])) {
            return [
                'title' => 'Error',
                'content' => 'You are not registered. Please register using /spotify',
                'flags' => 64,
                'color' => hexdec('eb3434')
            ];
        }

        $state = $users[$user_id]['playlist_gen'];

        $state = !$state;

        $users[$user_id]['playlist_gen'] = $state;
        file_put_contents(__DIR__.'/../../../users.json', json_encode($users, JSON_PRETTY_PRINT));


        return [
            'title' => 'Toggled Playlist Generator',
            'content' => "Current State: ".($state ? 'On' : 'Off'),
            'flags' => 64,
            'color' => hexdec('34ebd8')
        ];
    }

}