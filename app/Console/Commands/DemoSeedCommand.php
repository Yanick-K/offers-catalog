<?php

namespace App\Console\Commands;

use App\Models\Offer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

use function Laravel\Prompts\text;

class DemoSeedCommand extends Command
{
    protected $signature = 'demo:seed {--offers= : Nombre d\'offres a creer} {--products= : Nombre de produits par offre}';

    protected $description = 'Seed demo offers and products with optional counts.';

    public function handle(): int
    {
        $offersOption = $this->option('offers');
        $productsOption = $this->option('products');

        if ($offersOption === null) {
            $offersOption = text(
                label: 'Nombre d\'offres a creer ?',
                default: '3',
                required: true,
                validate: static function (string $value): ?string {
                    if (! ctype_digit($value)) {
                        return 'Entrez un nombre entier.';
                    }

                    if ((int) $value < 1) {
                        return 'Le nombre d\'offres doit etre >= 1.';
                    }

                    return null;
                }
            );
        }

        if ($productsOption === null) {
            $productsOption = text(
                label: 'Nombre de produits par offre ?',
                default: '3',
                required: true,
                validate: static function (string $value): ?string {
                    if (! ctype_digit($value)) {
                        return 'Entrez un nombre entier.';
                    }

                    if ((int) $value < 0) {
                        return 'Le nombre de produits doit etre >= 0.';
                    }

                    return null;
                }
            );
        }

        $offers = (int) $offersOption;
        $products = (int) $productsOption;

        if ($offers < 1) {
            $this->error('Le nombre d\'offres doit etre >= 1.');

            return self::FAILURE;
        }

        if ($products < 0) {
            $this->error('Le nombre de produits doit etre >= 0.');

            return self::FAILURE;
        }

        $this->ensureAdminUser();

        $disk = Storage::disk('public');
        $disk->makeDirectory('seed/offers');
        $disk->makeDirectory('seed/products');

        for ($i = 0; $i < $offers; $i++) {
            $offerImage = $this->storeRemoteImage('seed/offers', 'offer-'.Str::uuid());
            $offer = Offer::factory()->create([
                'image' => $offerImage,
            ]);

            for ($j = 0; $j < $products; $j++) {
                $productImage = $this->storeRemoteImage(
                    'seed/products',
                    'product-'.$offer->id.'-'.Str::uuid()
                );

                Product::factory()
                    ->for($offer)
                    ->create([
                        'image' => $productImage,
                    ]);
            }
        }

        $this->info(sprintf('Seeded %d offers and %d products per offer.', $offers, $products));

        return self::SUCCESS;
    }

    private function ensureAdminUser(): void
    {
        if (User::query()->where('email', 'test@example.com')->exists()) {
            return;
        }

        User::factory()->admin()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    private function storeRemoteImage(string $directory, string $seed): string
    {
        $path = sprintf('%s/%s', trim($directory, '/'), $seed);
        $url = sprintf('https://picsum.photos/seed/%s/640/480', $seed);

        try {
            $response = Http::timeout(10)->retry(2, 200)->get($url);
        } catch (Throwable) {
            return $this->createSvg($path.'.svg', 'Demo image');
        }

        if (! $response->successful()) {
            return $this->createSvg($path.'.svg', 'Demo image');
        }

        $extension = $this->extensionFromContentType($response->header('Content-Type'));
        $imagePath = $path.'.'.$extension;

        Storage::disk('public')->put($imagePath, $response->body());

        return $imagePath;
    }

    private function createSvg(string $path, string $label): string
    {
        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="640" height="480" viewBox="0 0 640 480"><rect width="640" height="480" fill="#f3f4f6"/><text x="50%%" y="50%%" font-family="Arial, sans-serif" font-size="24" fill="#111827" text-anchor="middle" dominant-baseline="middle">%s</text></svg>',
            e($label)
        );

        Storage::disk('public')->put($path, $svg);

        return $path;
    }

    private function extensionFromContentType(?string $contentType): string
    {
        $type = strtolower((string) $contentType);

        if (str_contains($type, 'image/png')) {
            return 'png';
        }

        if (str_contains($type, 'image/webp')) {
            return 'webp';
        }

        if (str_contains($type, 'image/avif')) {
            return 'avif';
        }

        return 'jpg';
    }
}
