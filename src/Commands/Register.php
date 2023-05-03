<?php

namespace Bot\Commands;

class Register
{
    public function getName(): string
    {
        return 'register';
    }
    public function getDescription(): string
    {
        return 'Register with the bot';
    }
    public function getOptions(): array
    {
        return [];
    }

    public function handle($args, $discord, $username, $user_id): array
    {
        $users = json_decode(file_get_contents(__DIR__.'/../../users.json'), true);

        if (isset($users[$user_id])) {
            $users[$user_id]['username'] = $username;
        } else {
            $users[$user_id] = [
                'username' => $username,
                'admin' => false,
                'playlist_gen' => false
            ];
        }

        file_put_contents(__DIR__.'/../../users.json', json_encode($users, JSON_PRETTY_PRINT));

        return [
            'title' => 'Registered',
            'content' => 'You have been registered',
            'flags' => 64,
            'color' => hexdec('34ebd8')
        ];
    }



}