<?php

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

    public function test_authenticated_user_can_create_offer(): void
    {
        Storage::fake('public');
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->post(route('offers.store'), [
            'name' => 'Offre Dashboard',
            'slug' => 'offre-dashboard',
            'description' => 'Une offre creee via le back-office.',
            'state' => OfferState::Draft->value,
            'image' => UploadedFile::fake()->image('offer.jpg'),
        ]);

        $response->assertRedirect(route('dashboard'));

        $offer = Offer::where('slug', 'offre-dashboard')->firstOrFail();

        Storage::disk('public')->assertExists($offer->image);
    }
}
