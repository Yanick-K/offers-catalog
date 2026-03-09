<?php

declare(strict_types=1);

namespace App\Domain\Shared\Contracts;

interface ImageUploader
{
    public function upload(ImageUpload $file, string $directory): string;

    public function delete(?string $path): void;
}
