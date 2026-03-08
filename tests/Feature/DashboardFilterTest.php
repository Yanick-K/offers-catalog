<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Offers\ValueObjects\OfferState;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_filters_by_name_and_slug(): void
    {
        $user = User::factory()->admin()->create();

        Offer::factory()->create([
            'name' => 'Alpha Offer',
            'slug' => 'alpha-offer',
            'state' => OfferState::Draft->value,
        ]);

        Offer::factory()->create([
            'name' => 'Beta Offer',
            'slug' => 'beta-offer',
            'state' => OfferState::Draft->value,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard', [
            'name' => 'Alpha',
            'slug' => 'alpha',
        ]));

        $response->assertOk();
        $response->assertSee('Alpha Offer');
        $response->assertDontSee('Beta Offer');
    }

    public function test_dashboard_filters_by_state(): void
    {
        $user = User::factory()->admin()->create();

        Offer::factory()->create([
            'name' => 'Published Offer',
            'slug' => 'published-offer',
            'state' => OfferState::Published->value,
        ]);

        Offer::factory()->create([
            'name' => 'Draft Offer',
            'slug' => 'draft-offer',
            'state' => OfferState::Draft->value,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard', [
            'state' => OfferState::Published->value,
        ]));

        $response->assertOk();
        $response->assertSee('Published Offer');
        $response->assertDontSee('Draft Offer');
    }

    public function test_dashboard_filter_button_submits_form(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('type="submit"', false);
        $response->assertSee('Filtrer');
    }
}
