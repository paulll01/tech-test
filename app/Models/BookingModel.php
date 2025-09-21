<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $uuid
 * @property int $car_park_id
 * @property string $customer_email
 * @property string $vehicle_reg
 * @property string $from_date
 * @property string $to_date
 * @property string $status
 * @property float|null $total_price
 * @property-read CarParkModel       $carPark
 * @property-read BookingDayModel[]  $bookingDays
 *
 * @method static \Database\Factories\BookingModelFactory factory()
 */
class BookingModel extends Model
{
    use HasFactory;

    protected $table = 'bookings';

    protected $fillable = [
        'uuid',
        'car_park_id',
        'customer_email',
        'vehicle_reg',
        'from_date',
        'to_date',
        'status',
        'total_price',
    ];

    protected $hidden = [
        'id',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function carPark(): BelongsTo
    {
        return $this->belongsTo(CarParkModel::class);
    }

    public function bookingDays(): HasMany
    {
        return $this->hasMany(BookingDayModel::class, 'booking_id');
    }
}
