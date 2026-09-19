<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'package_id',
        'package_name',
        'name',
        'email',
        'phone',
        'travel_date',
        'guests',
        'message',
        'is_read',
    ];

    protected $casts = [
        'travel_date' => 'date',
        'is_read' => 'boolean',
    ];
}
