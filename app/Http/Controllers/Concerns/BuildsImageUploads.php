<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use App\Application\Shared\Files\UploadedImage;
use App\Domain\Shared\Contracts\ImageUpload;
use Illuminate\Http\UploadedFile;
use RuntimeException;

trait BuildsImageUploads
{
    protected function toImageUpload(?UploadedFile $file): ?ImageUpload
    {
        if (! $file) {
            return null;
        }

        $path = $file->getRealPath();
        if ($path === false) {
            throw new RuntimeException('Unable to read uploaded file path.');
        }

        $extension = $file->getClientOriginalExtension();
        if ($extension === '') {
            $extension = (string) $file->extension();
        }

        return new UploadedImage($path, $extension);
    }
}
