<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $start_date
 * @property string $end_date
 * @property float $weekday_price
 * @property float $weekend_price
 * @property int $car_park_id
 *
 * @method static \Database\Factories\PricingSeasonModelFactory factory()
 */
class PricingSeasonModel extends Model
{
    use HasFactory;

    protected $table = 'pricing_seasons';

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'weekday_price',
        'weekend_price',
        'car_park_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'weekday_price' => 'decimal:2',
        'weekend_price' => 'decimal:2',
    ];

    protected $hidden = [
        'id',
    ];

    public function carPark(): BelongsTo
    {
        return $this->belongsTo(CarParkModel::class, 'car_park_id');
    }
}
