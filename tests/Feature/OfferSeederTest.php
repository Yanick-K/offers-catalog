<?php

namespace Tests\Feature;

use Database\Seeders\OfferSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OfferSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_offer_seeder_uses_local_images_without_network(): void
    {
        Storage::fake('public');
        Http::fake();

        $this->seed(OfferSeeder::class);

        Http::assertNothingSent();
        Storage::disk('public')->assertExists('seed/offers/offer-offre-starter.svg');
    }
}
