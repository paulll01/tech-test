<?php

namespace App\Providers;

use App\Repositories\Booking\BookingRepository;
use App\Repositories\Booking\IBookingRepository;
use App\Repositories\BookingDay\BookingDayRepository;
use App\Repositories\BookingDay\IBookingDayRepository;
use App\Repositories\PricingSeason\IPricingSeasonRepository;
use App\Repositories\PricingSeason\PricingSeasonRepository;
use App\Services\ApplicationServices\Availability\CheckAvailabilityService;
use App\Services\ApplicationServices\Availability\ICheckAvailabilityService;
use App\Services\ApplicationServices\Booking\AmendBookingService;
use App\Services\ApplicationServices\Booking\CancelBookingService;
use App\Services\ApplicationServices\Booking\CreateBookingService;
use App\Services\ApplicationServices\Booking\IAmendBookingService;
use App\Services\ApplicationServices\Booking\ICancelBookingService;
use App\Services\ApplicationServices\Booking\ICreateBookingService;
use App\Services\Booking\BookingOwnershipService;
use App\Services\Booking\IBookingOwnershipService;
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
        $this->app->bind(ICreateBookingService::class, CreateBookingService::class);
        $this->app->bind(IAmendBookingService::class, AmendBookingService::class);
        $this->app->bind(ICancelBookingService::class, CancelBookingService::class);
        $this->app->bind(IBookingOwnershipService::class, BookingOwnershipService::class);
        // Repositories
        $this->app->bind(IBookingDayRepository::class, BookingDayRepository::class);
        $this->app->bind(IPricingSeasonRepository::class, PricingSeasonRepository::class);
        $this->app->bind(IBookingRepository::class, BookingRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
