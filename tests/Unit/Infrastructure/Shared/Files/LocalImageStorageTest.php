<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Shared\Files;

use App\Infrastructure\Shared\Files\LocalImageStorage;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LocalImageStorageTest extends TestCase
{
    public function testStoreCreatesSvgLocally(): void
    {
        Storage::fake('public');

        $store = new LocalImageStorage;
        $path = $store->store('seed/offers', 'local-seed', 'Local');

        $this->assertStringEndsWith('.svg', $path);
        Storage::disk('public')->assertExists($path);
    }
}
