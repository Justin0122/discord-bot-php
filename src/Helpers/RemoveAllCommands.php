<?php

namespace Bot\Helpers;

use Discord\Slash\RegisterClient;

class RemoveAllCommands
{
    //remove all commands from the bot
    public static function removeAllCommands($discord): void
    {
        $client = new RegisterClient($_ENV['DISCORD_BOT_TOKEN']);
        $commands = $client->getCommands();
        foreach ($commands as $command) {
            $client->deleteCommand($command);
        }
    }


}