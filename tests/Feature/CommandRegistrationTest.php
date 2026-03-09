<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class CommandRegistrationTest extends TestCase
{
    public function testDemoSeedCommandIsRegistered(): void
    {
        $commands = array_keys(Artisan::all());

        $this->assertContains('demo:seed', $commands);
    }
}
