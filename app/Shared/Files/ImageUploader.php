<?php

declare(strict_types=1);

namespace App\Shared\Files;

use Illuminate\Http\UploadedFile;

interface ImageUploader
{
    public function upload(UploadedFile $file, string $directory): string;

    public function delete(?string $path): void;
}
