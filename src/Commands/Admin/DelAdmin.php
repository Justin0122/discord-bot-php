<?php

namespace Bot\Commands\Admin;

use Bot\Helpers\ErrorHandler;

class DelAdmin
{
    public function getName(): string
    {
        return 'deladmin';
    }
    public function getDescription(): string
    {
        return 'Delete an admin';
    }

    public function getGuildId(): ?string
    {
        return null;
    }

    public function getOptions(): array
    {
        return [
            [
                'name' => 'user',
                'description' => 'The user to delete',
                'type' => 6,
                'required' => true
            ]
        ];
    }

    public function handle($args, $discord, $username, $user_id)
    {

        $users = json_decode(file_get_contents(__DIR__.'/../../../users.json'), true);

        if (!isset($users[$user_id])) {
            return ErrorHandler::handle('User not found');
        }

        if (!$users[$user_id]['admin']) {
            return ErrorHandler::handle('User is not an admin');
        }
        if (isset($users[$args['user']])) {
            $user = $users[$args['user']]['username'];
        } else {
            $user = $args['user'];
        }

        $user = $users[$user_id]['username'];
        $users[$user_id]['admin'] = false;
        file_put_contents(__DIR__.'/../../../users.json', json_encode($users, JSON_PRETTY_PRINT));

        return [
            'title' => 'Deleted Admin',
            'content' => "Deleted admin for {$user}",
            'color' => hexdec('34ebd8')
        ];

    }

}