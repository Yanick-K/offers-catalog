<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Files;

use App\Infrastructure\Files\LocalImageStore;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LocalImageStoreTest extends TestCase
{
    public function test_store_creates_svg_locally(): void
    {
        Storage::fake('public');

        $store = new LocalImageStore;
        $path = $store->store('seed/offers', 'local-seed', 'Local');

        $this->assertStringEndsWith('.svg', $path);
        Storage::disk('public')->assertExists($path);
    }
}
