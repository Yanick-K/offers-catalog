<?php

declare(strict_types=1);

namespace App\Infrastructure\Files;

use App\Domain\Shared\ValueObjects\ImageExtension;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LocalImageStore
{
    public function store(string $directory, string $seed, string $label = 'Demo image'): string
    {
        $normalized = trim($directory, '/');
        Storage::disk('public')->makeDirectory($normalized);

        $filename = Str::slug($seed);
        if ($filename === '') {
            $filename = Str::random(8);
        }

        $path = sprintf('%s/%s.%s', $normalized, $filename, ImageExtension::Svg->value);

        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="640" height="480" viewBox="0 0 640 480"><rect width="640" height="480" fill="#f3f4f6"/><text x="50%%" y="50%%" font-family="Arial, sans-serif" font-size="24" fill="#111827" text-anchor="middle" dominant-baseline="middle">%s</text></svg>',
            e($label)
        );

        Storage::disk('public')->put($path, $svg);

        return $path;
    }
}
