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

    public function handle(){

        return [
            'title' => 'Toggle Playlist Generator',
            'content' => "Playlist Generator Toggled!",
            'flags' => 64,
            'color' => hexdec('34ebd8')
        ];
    }

}