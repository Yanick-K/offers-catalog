<?php

declare(strict_types=1);

namespace Tests\Feature;

use Database\Seeders\OfferSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OfferSeederTest extends TestCase
{
    use RefreshDatabase;

    public function testOfferSeederUsesLocalImagesWithoutNetwork(): void
    {
        Storage::fake('public');
        Http::fake();

        $this->seed(OfferSeeder::class);

        Http::assertNothingSent();
        Storage::disk('public')->assertExists('seed/offers/offer-starter-offer.svg');
    }
}
