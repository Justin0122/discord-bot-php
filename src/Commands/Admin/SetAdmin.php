<?php

namespace Bot\Commands\Admin;

class SetAdmin
{
    public function getName(): string
    {
        return 'setadmin';
    }
    public function getDescription(): string
    {
        return 'Add an admin';
    }
    public function getOptions(): array
    {
        return [
            [
                'name' => 'user',
                'description' => 'The user to add',
                'type' => 6,
                'required' => true
            ]
        ];
    }

    public function handle($args, $discord, $username, $user_id)
    {

        $users = json_decode(file_get_contents(__DIR__.'/../../../users.json'), true);

        if (!isset($users[$user_id])) {
            return [
                'title' => 'Error',
                'content' => 'User not found',
                'flags' => 64,
                'color' => hexdec('eb3434')
            ];
        }


        if (isset($users[$args['user']])) {
            $user = $users[$args['user']]['username'];
        } else {
            $user = $args['user'];
        }

        if ($users[$user_id]['admin']) {
            return [
                'title' => 'Error',
                'content' => 'User is already an admin',
                'flags' => 64,
                'color' => hexdec('eb3434')
            ];
        }

        $users[$user_id]['admin'] = true;
        file_put_contents(__DIR__.'/../../../users.json', json_encode($users, JSON_PRETTY_PRINT));

        return [
            'title' => 'Add Admin',
            'content' => "Added admin role for {$user}",
            'color' => hexdec('34ebd8')
        ];

    }

}