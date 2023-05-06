<?php

namespace Bot\Helpers;

class ErrorHandler
{
    public static function handle($message): array
    {
        return [
            'title' => 'Error',
            'content' => $message,
            'flags' => 64,
            'color' => hexdec('eb3434')
        ];
    }

    public static function handleException($e): array
    {
        return [
            'title' => 'Error',
            'content' => $e->getMessage(),
            'flags' => 64,
            'color' => hexdec('eb3434')
        ];
    }
}