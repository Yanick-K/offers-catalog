<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Offer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PublicOfferCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_cache_version_is_bumped_on_offer_create(): void
    {
        Cache::forget('public_offers:version');

        Offer::factory()->create();

        $this->assertSame(1, Cache::get('public_offers:version'));
    }
}
