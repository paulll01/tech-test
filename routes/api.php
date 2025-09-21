<?php

use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\BookingController;
use Illuminate\Support\Facades\Route;

Route::get('/{carPark}/availability', [AvailabilityController::class, 'check'])->name('availability.check');

Route::post('/{carPark}/bookings', [BookingController::class, 'store'])->name('bookings.store');

Route::patch('/bookings/{booking}', [BookingController::class, 'update'])->name('bookings.amend');

Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
