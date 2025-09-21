<?php

use App\Http\Controllers\Api\AvailabilityController;
use Illuminate\Support\Facades\Route;

Route::get('/{carPark}/availability', [AvailabilityController::class, 'check']);
