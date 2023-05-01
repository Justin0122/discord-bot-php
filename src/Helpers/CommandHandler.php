<?php

namespace Bot\Helpers;

class CommandHandler{
    public static function runCommand($command, $channel, $discord)
    {
        $command = CommandRegistrar::getCommand($command);
        if ($command) {
            return $command->handle($channel, $command->getOptions(), $discord);
        } else {
            $channel->sendMessage("Command not found");
            return '';
        }
    }


}
