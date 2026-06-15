<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class ImageUploadService
{
    public function upload(UploadedFile $file, string $folder, int $maxWidth = 1200): string
    {
        $image = Image::read($file->getRealPath())
            ->scaleDown(width: $maxWidth)
            ->toWebp(quality: 85);

        $path = "{$folder}/" . Str::uuid() . '.webp';

        Storage::disk('r2')->put($path, (string) $image, 'public');

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('r2')->delete($path);
        }
    }
}
