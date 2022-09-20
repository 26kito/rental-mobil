<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarDescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_id', 'car_model_year', 'color', 'capacity', 'no_plate'
    ];

    public function car() {
        return $this->belongsTo(Car::class);
    }
}
