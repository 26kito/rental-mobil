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

  public function carDescription()
  {
    return $this->hasOne(CarDescription::class);
  }
}
