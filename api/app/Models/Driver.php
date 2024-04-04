<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'latitude', 'longitude', 'availability', 'last_location'
    ];

    // Relación con los servicios
    public function services()
    {
        return $this->hasMany(Service::class);
    }
}
