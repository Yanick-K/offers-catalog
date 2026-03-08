<?php

namespace App\Infrastructure\Files;

use App\Shared\Files\ImageUploader;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class StorageImageUploader implements ImageUploader
{
    public function upload(UploadedFile $file, string $directory): string
    {
        $path = $file->store($directory, 'public');

        if (! $path) {
            throw new RuntimeException('Failed to store image.');
        }

        return $path;
    }

    public function delete(?string $path): void
    {
        if (! $path) {
            return;
        }

        $disk = Storage::disk('public');

        if ($disk->exists($path)) {
            $disk->delete($path);
        }
    }
}
