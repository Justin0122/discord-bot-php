<?php

namespace Bot\Helpers;

class CommandHandler{
    public static function runCommand($command, $args, $discord, $username)
    {
        $commandObj = CommandRegistrar::getCommand($command);
        if ($commandObj) {
            return $commandObj->handle($args, $discord, $username);
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
