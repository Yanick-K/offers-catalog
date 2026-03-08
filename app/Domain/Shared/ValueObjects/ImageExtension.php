<?php

namespace App\Domain\Shared\ValueObjects;

enum ImageExtension: string
{
    case Jpg = 'jpg';
    case Png = 'png';
    case Webp = 'webp';
    case Avif = 'avif';
    case Svg = 'svg';

    public static function fromContentType(?string $contentType): self
    {
        $type = strtolower((string) $contentType);

        if (str_contains($type, 'image/png')) {
            return self::Png;
        }

        if (str_contains($type, 'image/webp')) {
            return self::Webp;
        }

        if (str_contains($type, 'image/avif')) {
            return self::Avif;
        }

        if (str_contains($type, 'image/svg')) {
            return self::Svg;
        }

        return self::Jpg;
    }
}
