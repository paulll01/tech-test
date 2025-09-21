<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $booking_id
 * @property int $car_park_id
 * @property string|\Illuminate\Support\Carbon $date
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read BookingModel $booking
 * @property-read CarParkModel $carPark
 */
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
