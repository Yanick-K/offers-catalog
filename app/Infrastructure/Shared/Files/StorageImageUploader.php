<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Files;

use App\Domain\Shared\Contracts\ImageUpload;
use App\Domain\Shared\Contracts\ImageUploader;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class StorageImageUploader implements ImageUploader
{
    public function upload(ImageUpload $file, string $directory): string
    {
        $extension = $file->extension();
        if ($extension === '') {
            $extension = 'bin';
        }

        $filename = sprintf('%s.%s', Str::random(40), $extension);
        $path = trim($directory, '/') . '/' . $filename;

        $stream = fopen($file->path(), 'rb');
        if ($stream === false) {
            throw new RuntimeException('Failed to read image content.');
        }

        $stored = Storage::disk('public')->put($path, $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }

        if (! $stored) {
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
