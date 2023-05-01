<?php

namespace Bot\Helpers;

class CommandHandler{
    public static function runCommand($command, $channel)
    {
        $command = CommandRegistrar::getCommand($command);
        if ($command) {
            return $command->handle($channel);
        } else {
            $channel->sendMessage("Command not found");
            return '';
        }
    }


}
