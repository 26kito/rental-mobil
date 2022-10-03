<?php

namespace Database\Seeders;

use App\Models\CarStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CarStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        CarStatus::create(['status' => 'available']);
        CarStatus::create(['status' => 'not available']);
    }
}
