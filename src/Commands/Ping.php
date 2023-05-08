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

    public function getGuildId(): ?string
    {
        return null;
    }

    public function handle(){

        return [
            'title' => 'Ping',
            'content' => "Pong!",
            'flags' => 64,
            'color' => hexdec('34ebd8')
        ];
    }

}