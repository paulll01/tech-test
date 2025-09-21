<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property int $capacity
 * @property float $default_weekday_price
 * @property float $default_weekend_price
 */
class CarParkModel extends Model
{
    use HasFactory;

    protected $table = 'car_parks';

    protected $fillable = [
        'uuid',
        'name',
        'capacity',
        'default_weekday_price',
        'default_weekend_price',
    ];

    protected $hidden = [
        'id',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(BookingModel::class);
    }

    public function bookingDays(): HasMany
    {
        return $this->hasMany(BookingDayModel::class);
    }

    public function pricingSeasons(): HasMany
    {
        return $this->hasMany(PricingSeasonModel::class);
    }
}
