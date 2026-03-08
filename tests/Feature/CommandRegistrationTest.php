<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class CommandRegistrationTest extends TestCase
{
    public function test_demo_seed_command_is_registered(): void
    {
        $commands = array_keys(Artisan::all());

        $this->assertContains('demo:seed', $commands);
    }
}
