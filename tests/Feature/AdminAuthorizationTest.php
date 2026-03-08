<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_dashboard(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertForbidden();
    }

    public function test_non_admin_cannot_access_offer_management(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $offer = Offer::factory()->create();

        $this->actingAs($user)
            ->get(route('offers.create'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('offers.show', $offer->id))
            ->assertForbidden();
    }
}
