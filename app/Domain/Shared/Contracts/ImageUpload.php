<?php

declare(strict_types=1);

namespace App\Domain\Shared\Contracts;

interface ImageUpload
{
    public function path(): string;

    public function extension(): string;
}
