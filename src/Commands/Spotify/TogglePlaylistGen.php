<?php

namespace Bot\Commands\Spotify;

use Bot\Helpers\ErrorHandler;

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

    public function getGuildId(): ?string
    {
        return null;
    }

    public function getOptions(): array
    {
        return [];
    }

    public function handle($args, $discord, $username, $user_id)
    {

    }
}