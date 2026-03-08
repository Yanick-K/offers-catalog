<?php

namespace App\Infrastructure\Files;

use App\Domain\Shared\ValueObjects\ImageExtension;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Throwable;

class RemoteImageStore
{
    public function store(string $directory, string $seed, string $fallbackLabel = 'Demo image'): string
    {
        $normalized = trim($directory, '/');
        Storage::disk('public')->makeDirectory($normalized);

        $path = sprintf('%s/%s', $normalized, $seed);
        $url = sprintf('https://picsum.photos/seed/%s/640/480', $seed);

        try {
            $response = Http::timeout(10)->retry(2, 200)->get($url);
        } catch (Throwable) {
            return $this->storeFallbackSvg($path.'.'.ImageExtension::Svg->value, $fallbackLabel);
        }

        if (! $response->successful()) {
            return $this->storeFallbackSvg($path.'.'.ImageExtension::Svg->value, $fallbackLabel);
        }

        $extension = ImageExtension::fromContentType($response->header('Content-Type'));
        $imagePath = $path.'.'.$extension->value;

        Storage::disk('public')->put($imagePath, $response->body());

        return $imagePath;
    }

    private function storeFallbackSvg(string $path, string $label): string
    {
        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="640" height="480" viewBox="0 0 640 480"><rect width="640" height="480" fill="#f3f4f6"/><text x="50%%" y="50%%" font-family="Arial, sans-serif" font-size="24" fill="#111827" text-anchor="middle" dominant-baseline="middle">%s</text></svg>',
            e($label)
        );

        Storage::disk('public')->put($path, $svg);

        return $path;
    }
}
