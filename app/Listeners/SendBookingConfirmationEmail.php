<?php

namespace App\Listeners;

use App\Events\Booking\BookingCreatedEvent;

class SendBookingConfirmationEmail
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(BookingCreatedEvent $event): void
    {
        // send email to the customer
    }
}
