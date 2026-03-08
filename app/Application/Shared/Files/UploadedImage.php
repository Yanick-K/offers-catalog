<?php

declare(strict_types=1);

namespace App\Application\Shared\Files;

use App\Domain\Shared\Contracts\ImageUpload;
use RuntimeException;

final readonly class UploadedImage implements ImageUpload
{
    public function __construct(private string $path, private string $extension)
    {
        if ($this->path === '') {
            throw new RuntimeException('Image path is required.');
        }
    }

    public function path(): string
    {
        return $this->path;
    }

    public function extension(): string
    {
        $normalized = ltrim($this->extension, '.');

        return strtolower($normalized);
    }
}
