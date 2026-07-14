<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use LogsActivity;
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'phone_number',
    ];

    public function meterReadings()
    {
        return $this->hasMany(MeterReading::class);
    }

    public function tenant()
    {
        return $this->hasOne(Tenant::class, 'user_id');
    }

    public function customNotifications()
    {
        return $this->hasMany(Notification::class, 'user_id')->latest();
    }
}
