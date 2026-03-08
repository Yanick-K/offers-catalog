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

    public function testNonAdminCannotAccessDashboard(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertForbidden();
    }

    public function testNonAdminCannotAccessOfferManagement(): void
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
