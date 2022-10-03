<?php

namespace Database\Seeders;

use App\Models\RentStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RentStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        RentStatus::create(['status' => 'pending']);
        RentStatus::create(['status' => 'accepted']);
        RentStatus::create(['status' => 'declined']);
    }
}
