<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Offers\ValueObjects\OfferState;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OfferManagementTest extends TestCase
{
    use RefreshDatabase;

    public function testAuthenticatedUserCanCreateOffer(): void
    {
        Storage::fake('public');
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->post(route('offers.store'), [
            'name' => 'Dashboard Offer',
            'slug' => 'dashboard-offer',
            'description' => 'An offer created from the back office.',
            'state' => OfferState::Draft->value,
            'image' => UploadedFile::fake()->image('offer.jpg'),
        ]);

        $response->assertRedirect(route('dashboard'));

        $offer = Offer::where('slug', 'dashboard-offer')->firstOrFail();

        Storage::disk('public')->assertExists($offer->image);
    }
}
