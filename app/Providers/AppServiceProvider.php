<?php

namespace App\Providers;

use App\Domain\Offers\Repositories\OfferRepository;
use App\Domain\Products\Repositories\ProductRepository;
use App\Infrastructure\Files\StorageImageUploader;
use App\Infrastructure\Repositories\EloquentOfferRepository;
use App\Infrastructure\Repositories\EloquentProductRepository;
use App\Models\Offer;
use App\Models\Product;
use App\Models\User;
use App\Observers\OfferObserver;
use App\Observers\ProductObserver;
use App\Shared\Files\ImageUploader;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(OfferRepository::class, EloquentOfferRepository::class);
        $this->app->bind(ProductRepository::class, EloquentProductRepository::class);
        $this->app->bind(ImageUploader::class, StorageImageUploader::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('admin', static fn (User $user): bool => (bool) $user->is_admin);

        Offer::observe(OfferObserver::class);
        Product::observe(ProductObserver::class);
    }
}
