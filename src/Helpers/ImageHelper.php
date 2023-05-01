<?php

namespace Bot\Helpers;

class ImageHelper
{

    public static function getRandomImage($command, $type): ?string
    {
        $path = 'src/Media/' . ucfirst($command) . '/' . $type;
        $files = scandir($path);
        $files = array_diff($files, ['.', '..']);
        try {
            $file = $files[array_rand($files)];
            return $path . '/' . $file;
        } catch (\Throwable $th) {
            return null;
        }
    }

    public static function spoilerImage($file): array|string
    {
        $fileParts = explode('/', $file);
        $fileName = $fileParts[count($fileParts) - 1];
        $newFile = __DIR__ . "/../../tmp/{$fileName}";
        $newFile = str_replace($fileName, 'SPOILER_' . $fileName, $newFile);
        copy($file, $newFile);
        return $newFile;
    }

    public static function deleteFiles(): void
    {
        $files = glob(__DIR__ . '/../../tmp/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}