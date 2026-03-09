<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Infrastructure\Shared\Seed\DemoSeedHandler;
use Illuminate\Console\Command;
use Laravel\Prompts\Note;
use Laravel\Prompts\TextPrompt;

class DemoSeedCommand extends Command
{
    protected $signature = 'demo:seed {--offers= : Number of offers to create} {--products= : Products per offer} {--remote : Use remote images from picsum.photos}';

    protected $description = 'Seed demo offers and products with optional counts (use --remote for picsum).';

    public function handle(DemoSeedHandler $handler): int
    {
        $offersOption = $this->option('offers');
        $productsOption = $this->option('products');

        if ($offersOption === null) {
            $offersOption = (new TextPrompt(
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
            ))->prompt();
        }

        if ($productsOption === null) {
            $productsOption = (new TextPrompt(
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
            ))->prompt();
        }

        $offers = (int) $offersOption;
        $products = (int) $productsOption;

        if ($offers < 1) {
            (new Note('Offers must be >= 1.', 'error'))->display();

            return self::FAILURE;
        }

        if ($products < 0) {
            (new Note('Products must be >= 0.', 'error'))->display();

            return self::FAILURE;
        }

        $useRemote = (bool) $this->option('remote');

        $handler->handle($offers, $products, $useRemote);

        (new Note(sprintf('Seeded %d offers and %d products per offer.', $offers, $products), 'info'))->display();

        return self::SUCCESS;
    }
}
