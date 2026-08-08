<?php

namespace App\Providers;

use App\Models\Buyer;
use App\Models\Product;
use App\Models\Vendor;
use App\Policies\DataModulePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Buyer::class, DataModulePolicy::class);
        Gate::policy(Vendor::class, DataModulePolicy::class);
        Gate::policy(Product::class, DataModulePolicy::class);
    }
}
