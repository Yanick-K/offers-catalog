<?php

namespace App\Domain\Products\ValueObjects;

enum ProductState: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Invisible = 'invisible';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Brouillon',
            self::Published => 'Publie',
            self::Invisible => 'Invisible',
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
