<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Files;

use App\Infrastructure\Files\RemoteImageStore;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RemoteImageStoreTest extends TestCase
{
    public function test_store_falls_back_to_svg_on_failure(): void
    {
        Storage::fake('public');
        Http::fake([
            '*' => Http::response('', 500),
        ]);

        $store = new RemoteImageStore;
        $path = $store->store('seed/offers', 'offline-seed', 'Fallback');

        $this->assertStringEndsWith('.svg', $path);
        Storage::disk('public')->assertExists($path);
    }
}
