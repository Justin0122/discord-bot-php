<?php

namespace Bot\Helpers;
use Discord\Slash\RegisterClient;

class CommandRegistrar
{
    public static function register(): void
    {
        $client = new RegisterClient($_ENV['DISCORD_BOT_TOKEN']);

        foreach (glob(__DIR__.'/../Commands/*.php') as $filename) {
            require_once $filename;
            $className = 'Bot\Commands\\'.basename($filename, '.php');
            $command = new $className();

            $client->createGlobalCommand(
                $command->getName(),
                $command->getDescription(),
                $command->getOptions()
            );
            echo "Registered command: {$command->getName()}", PHP_EOL;
        }
    }

    public static function getCommand($command)
    {
        foreach (glob(__DIR__.'/../Commands/*.php') as $filename) {
            require_once $filename;
            $className = 'Bot\Commands\\'.basename($filename, '.php');
            $commandClass = new $className();
            if ($commandClass->getName() == $command) {
                return $commandClass;
            }
        }
        return null;
    }
}
