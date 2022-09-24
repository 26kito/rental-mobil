<?php

namespace Database\Seeders;

use App\Models\Car;
use App\Models\CarDescription;
use Illuminate\Database\Seeder;

class CarSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    Car::create([
      'brand_car' => 'Toyota',
      'owner_id' => 1
    ]);
    CarDescription::create([
      'car_id' => 1,
      'car_model_year' => 2022,
      'color' => 'Red',
      'capacity' => 4,
      'no_plate' => 'H4R1 S3N1N'
    ]);

    Car::create([
      'brand_car' => 'Daihatsu',
      'owner_id' => 2
    ]);
    CarDescription::create([
      'car_id' => 2,
      'car_model_year' => 1996,
      'color' => 'Green',
      'capacity' => 2,
      'no_plate' => 'H4R1 S3L45A'
    ]);

    Car::create([
      'brand_car' => 'Jeep',
      'owner_id' => 3
    ]);
    CarDescription::create([
      'car_id' => 3,
      'car_model_year' => 2013,
      'color' => 'Blue',
      'capacity' => 10,
      'no_plate' => 'H4R1 R48U'
    ]);

    Car::create([
      'brand_car' => 'Mustibisa',
      'owner_id' => 1
    ]);
    CarDescription::create([
      'car_id' => 4,
      'car_model_year' => 2006,
      'color' => 'Yellow',
      'capacity' => 4,
      'no_plate' => 'H4R1 K4M15'
    ]);

    Car::create([
      'brand_car' => 'Hyundai',
      'owner_id' => 2
    ]);
    CarDescription::create([
      'car_id' => 5,
      'car_model_year' => 1987,
      'color' => 'Black',
      'capacity' => 4,
      'no_plate' => 'H4R1 JUM47'
    ]);

    Car::create([
      'brand_car' => 'Isuzu',
      'owner_id' => 3
    ]);
    CarDescription::create([
      'car_id' => 6,
      'car_model_year' => 1999,
      'color' => 'Pink',
      'capacity' => 4,
      'no_plate' => 'H4R1 S4B7U'
    ]);

    Car::create([
      'brand_car' => 'Kawasaki',
      'owner_id' => 1
    ]);
    CarDescription::create([
      'car_id' => 7,
      'car_model_year' => 2017,
      'color' => 'Brown',
      'capacity' => 2,
      'no_plate' => 'H4R1 M1N99U'
    ]);

    Car::create([
      'brand_car' => 'Nissan',
      'owner_id' => 2
    ]);
    CarDescription::create([
      'car_id' => 8,
      'car_model_year' => 2001,
      'color' => 'Grey',
      'capacity' => 6,
      'no_plate' => 'H4R1 L1BUR'
    ]);
  }
}
