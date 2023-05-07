<?php

namespace Bot\Helpers;

class CommandHandler{
    public static function runCommand($command, $args, $discord, $username, $user_id): array
    {
        $commandObj = CommandRegistrar::getCommand($command, $username, $user_id);
        if ($commandObj) {
            return $commandObj->handle($args, $discord, $username, $user_id);
        } else {
            return [
                'title' => 'Command not found',
                'content' => '',
                'flags' => 0,
                'color' => hexdec('FF0000')
            ];
        }
    }
}
