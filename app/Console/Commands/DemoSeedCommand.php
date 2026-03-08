<?php

namespace App\Console\Commands;

use App\Infrastructure\Seed\DemoSeedHandler;
use Illuminate\Console\Command;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\text;

class DemoSeedCommand extends Command
{
    protected $signature = 'demo:seed {--offers= : Number of offers to create} {--products= : Products per offer}';

    protected $description = 'Seed demo offers and products with optional counts.';

    public function handle(DemoSeedHandler $handler): int
    {
        $offersOption = $this->option('offers');
        $productsOption = $this->option('products');

        if ($offersOption === null) {
            $offersOption = text(
                label: 'How many offers to create?',
                default: '3',
                required: true,
                validate: static function (string $value): ?string {
                    if (! ctype_digit($value)) {
                        return 'Enter a whole number.';
                    }

                    if ((int) $value < 1) {
                        return 'Offers must be >= 1.';
                    }

                    return null;
                }
            );
        }

        if ($productsOption === null) {
            $productsOption = text(
                label: 'How many products per offer?',
                default: '3',
                required: true,
                validate: static function (string $value): ?string {
                    if (! ctype_digit($value)) {
                        return 'Enter a whole number.';
                    }

                    if ((int) $value < 0) {
                        return 'Products must be >= 0.';
                    }

                    return null;
                }
            );
        }

        $offers = (int) $offersOption;
        $products = (int) $productsOption;

        if ($offers < 1) {
            error('Offers must be >= 1.');

            return self::FAILURE;
        }

        if ($products < 0) {
            error('Products must be >= 0.');

            return self::FAILURE;
        }

        $handler->handle($offers, $products);

        info(sprintf('Seeded %d offers and %d products per offer.', $offers, $products));

        return self::SUCCESS;
    }
}
