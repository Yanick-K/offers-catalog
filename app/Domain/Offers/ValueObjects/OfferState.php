<?php

declare(strict_types=1);

namespace App\Domain\Offers\ValueObjects;

enum OfferState: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Hidden = 'hidden';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Published => 'Published',
            self::Hidden => 'Hidden',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        $labels = [];

        foreach (self::cases() as $state) {
            $labels[$state->value] = $state->label();
        }

        return $labels;
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $state): string => $state->value,
            self::cases()
        );
    }
}
