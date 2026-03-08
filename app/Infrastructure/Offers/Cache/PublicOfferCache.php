<?php

declare(strict_types=1);

namespace App\Infrastructure\Offers\Cache;

use Closure;
use Illuminate\Support\Facades\Cache;

final class PublicOfferCache
{
    private const VERSION_KEY = 'public_offers:version';

    public static function remember(int $page, int $perPage, Closure $callback): mixed
    {
        return Cache::remember(
            self::key($page, $perPage),
            now()->addMinutes(5),
            $callback
        );
    }

    public static function bumpVersion(): void
    {
        $version = (int) Cache::get(self::VERSION_KEY, 0);
        Cache::put(self::VERSION_KEY, $version + 1);
    }

    private static function key(int $page, int $perPage): string
    {
        $version = (int) Cache::get(self::VERSION_KEY, 0);

        return sprintf('public_offers:v%s:per_page:%s:page:%s', $version, $perPage, $page);
    }
}
