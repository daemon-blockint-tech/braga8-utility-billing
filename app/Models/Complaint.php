<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    use LogsActivity;
    
    protected $fillable = [
        'user_id',    
        'title',     
        'reported_by', 
        'role', 
        'report_date', 
        'status', 
        'description', 
        'solution',
        'image'
    ];

    protected $casts = [
        'report_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}