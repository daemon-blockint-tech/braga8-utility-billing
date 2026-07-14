<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class UtilityMeter extends Model
{
    use LogsActivity;

    protected $table = 'utility_meters';

    protected $fillable = [
        'unit_id',
        'meter_type',
        'meter_number',
        'multiplier',
        'power_capacity',
        'tariff_id',
    ];

    protected static function booted()
    {
        static::creating(function ($meter) {
            $meter->meter_category = 'postpaid';
        });
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class)->withDefault();
    }

    public function tariff()
    {
        return $this->belongsTo(Tariff::class);
    }

    public function readings()
    {
        return $this->hasMany(MeterReading::class, 'meter_id');
    }

    public function latestReading()
    {
        return $this->hasOne(MeterReading::class, 'meter_id')
            ->latestOfMany('recorded_at');
    }

    public function previousReading()
    {
        return $this->hasOne(MeterReading::class, 'meter_id')
            ->where('status', 'checked')
            ->orderByDesc('recorded_at')
            ->skip(1)
            ->take(1);
    }
}