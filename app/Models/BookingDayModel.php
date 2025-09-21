<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingDayModel extends Model
{
    use HasFactory;

    protected $table = 'booking_days';

    protected $fillable = [
        'booking_id',
        'car_park_id',
        'date',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(BookingModel::class);
    }

    public function carPark(): BelongsTo
    {
        return $this->belongsTo(CarParkModel::class);
    }
}
