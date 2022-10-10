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
        Car::factory(50)
            ->has(CarDescription::factory())
            ->create();
    }
}
