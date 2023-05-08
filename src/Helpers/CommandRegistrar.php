<?php

namespace Bot\Helpers;
use Discord\Slash\RegisterClient;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class CommandRegistrar
{
    public static function register(): void
    {
        $client = new RegisterClient($_ENV['DISCORD_BOT_TOKEN']);

        $dirIterator = new RecursiveDirectoryIterator(__DIR__.'/../Commands');
        $iterator = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::LEAVES_ONLY);
        $phpFiles = new RegexIterator($iterator, '/^.+\.php$/i', RegexIterator::GET_MATCH);

        foreach ($phpFiles as $phpFile) {
            $filename = $phpFile[0];
            require_once $filename;

            $className = 'Bot\\Commands\\' . str_replace('/', '\\', substr($filename, strlen(__DIR__.'/../Commands/'), -4));

            if (class_exists($className)) {
                $command = new $className();
                if ($command->getGuildId()) {
                    $client->createGuildSpecificCommand(
                        $command->getGuildId(),
                        $command->getName(),
                        $command->getDescription(),
                        $command->getOptions()
                    );
                    echo "Registered guild command: {$command->getName()} in guild: {$command->getGuildId()}", PHP_EOL;
                } else {
                    $client->createGlobalCommand(
                        $command->getName(),
                        $command->getDescription(),
                        $command->getOptions()
                    );
                    echo "Registered command: {$command->getName()}", PHP_EOL;
                }
            }
        }
    }

    public static function getCommandByName($command)
    {
        foreach (glob(__DIR__.'/../Commands/*.php') as $filename) {
            require_once $filename;
            $className = 'Bot\Commands\\'.basename($filename, '.php');
            $commandClass = new $className();
            if ($commandClass->getName() == $command) {
                return $commandClass;
            }
        }
        foreach (glob(__DIR__.'/../Commands/*', GLOB_ONLYDIR) as $dir) {
            foreach (glob($dir.'/*.php') as $filename) {
                require_once $filename;
                $className = 'Bot\Commands\\' . str_replace('/', '\\', substr($filename, strpos($filename, 'Commands') + strlen('Commands') + 1, -4));
                $commandClass = new $className();
                if ($commandClass->getName() == $command) {
                    return $commandClass;
                }
            }
        }
        return null;
    }
}
