<?php

namespace App\Providers;

use App\Repositories\BookingDay\BookingDayRepository;
use App\Repositories\BookingDay\IBookingDayRepository;
use App\Repositories\PricingSeason\IPricingSeasonRepository;
use App\Repositories\PricingSeason\PricingSeasonRepository;
use App\Services\ApplicationServices\Availability\CheckAvailabilityService;
use App\Services\ApplicationServices\Availability\ICheckAvailabilityService;
use App\Services\BookingDay\BookingDayService;
use App\Services\BookingDay\IBookingDayService;
use App\Services\Pricing\IQuotePricingService;
use App\Services\Pricing\QuotePricingService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Services
        $this->app->bind(IBookingDayService::class, BookingDayService::class);
        $this->app->bind(IQuotePricingService::class, QuotePricingService::class);
        $this->app->bind(ICheckAvailabilityService::class, CheckAvailabilityService::class);
        // Repositories
        $this->app->bind(IBookingDayRepository::class, BookingDayRepository::class);
        $this->app->bind(IPricingSeasonRepository::class, PricingSeasonRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
