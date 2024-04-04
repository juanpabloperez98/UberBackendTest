<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'latitude_origin',
        'longitude_origin',
        'latitude_destination',
        'longitude_destination',
        'status',
        'driver_id'
    ];

    // Relación con el usuario que solicita el servicio
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación con el conductor asignado al servicio
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
