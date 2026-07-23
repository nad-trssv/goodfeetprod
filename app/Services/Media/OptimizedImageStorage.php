<?php

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class OptimizedImageStorage
{
    public function store(
        UploadedFile $file,
        string $directory,
        string $baseName,
        int $maxWidth = 1600,
        int $maxHeight = 1200,
        int $quality = 82,
    ): string {
        $source = @imagecreatefromstring(file_get_contents($file->getRealPath()));
        if ($source === false) {
            throw new RuntimeException('The uploaded image could not be decoded.');
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min(1, $maxWidth / $width, $maxHeight / $height);
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));
        $target = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($target, false);
        imagesavealpha($target, true);
        imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        ob_start();
        $written = imagewebp($target, null, $quality);
        $contents = ob_get_clean();
        imagedestroy($source);
        imagedestroy($target);

        if (!$written || $contents === false) {
            throw new RuntimeException('The uploaded image could not be optimized.');
        }

        $slug = Str::slug($baseName) ?: 'image';
        $path = trim($directory, '/').'/'.$slug.'-'.Str::lower(Str::random(10)).'.webp';
        Storage::disk('public')->put($path, $contents);

        return $path;
    }
}
