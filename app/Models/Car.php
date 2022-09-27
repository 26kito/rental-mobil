<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_car', 'status', 'owner_id'
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];

    public function carDescription() {
        return $this->hasOne(CarDescription::class);
    }

    public function user() {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
