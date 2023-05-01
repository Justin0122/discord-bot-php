<?php


namespace Bot\Commands;

class Ping
{
    public function getName(): string
    {
        return 'ping';
    }
    public function getDescription(): string
    {
        return 'Ping the bot to check if it is online';
    }
    public function getOptions(): array
    {
        return [];
    }

    public function handle($channel): string
    {
        return 'Pong!';
    }


}
