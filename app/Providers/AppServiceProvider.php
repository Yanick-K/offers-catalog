<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Offers\Repositories\OfferRepository;
use App\Domain\Products\Repositories\ProductRepository;
use App\Domain\Shared\Contracts\ImageUploader;
use App\Infrastructure\Offers\Repositories\EloquentOfferRepository;
use App\Infrastructure\Products\Repositories\EloquentProductRepository;
use App\Infrastructure\Shared\Files\StorageImageUploader;
use App\Models\Offer;
use App\Models\Product;
use App\Models\User;
use App\Observers\OfferObserver;
use App\Observers\ProductObserver;
use Illuminate\Console\Command as ConsoleCommand;
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
        $this->app->resolving(ConsoleCommand::class, function (ConsoleCommand $command, $app): void {
            $command->setLaravel($app);
        });
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
