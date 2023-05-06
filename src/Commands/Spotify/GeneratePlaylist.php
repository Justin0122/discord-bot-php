<?php

namespace Bot\Commands\Spotify;

use Bot\Helpers\ErrorHandler;
use bot\Scheduler\PlaylistScheduler;

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
        return [];
    }

    public function handle($args, $discord, $username, $user_id): array
    {

    }

}