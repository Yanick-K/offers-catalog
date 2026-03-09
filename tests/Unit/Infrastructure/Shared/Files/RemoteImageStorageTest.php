<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Shared\Files;

use App\Infrastructure\Shared\Files\RemoteImageStorage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RemoteImageStorageTest extends TestCase
{
    public function testStoreFallsBackToSvgOnFailure(): void
    {
        Storage::fake('public');
        Http::fake([
            '*' => Http::response('', 500),
        ]);

        $store = new RemoteImageStorage;
        $path = $store->store('seed/offers', 'offline-seed', 'Fallback');

        $this->assertStringEndsWith('.svg', $path);
        Storage::disk('public')->assertExists($path);
    }
}
