<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class MeterReading extends Model
{
    use LogsActivity;
  protected $fillable = [
    'meter_id', 
    'user_id', 
    'reading_value', 
    'photo_path', 
    'latitude',       
    'longitude',      
    'location_address',
    'recorded_at', 
    'description', 
    'status',
];
    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    public function meter() {
        return $this->belongsTo(UtilityMeter::class, 'meter_id');
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}