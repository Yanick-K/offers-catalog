<?php

namespace App\Shared\Files;

use Illuminate\Http\UploadedFile;

interface ImageUploader
{
    public function upload(UploadedFile $file, string $directory): string;

    public function delete(?string $path): void;
}
