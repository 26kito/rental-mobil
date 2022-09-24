<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    User::create([
      'name' => 'adics',
      'email' => 'adics@gmail.com',
      'address' => 'Jakarta',
      'mobile_phone' => '628123456789',
      'password' => bcrypt('hehe1234'),
      'role_id' => 2
    ]);
    User::create([
      'name' => 'johndoe',
      'email' => 'johndoe@gmail.com',
      'address' => 'Bandung',
      'mobile_phone' => '628123456788',
      'password' => bcrypt('hehe1234'),
      'role_id' => 2
    ]);
    User::create([
      'name' => 'markhill',
      'email' => 'markhill@gmail.com',
      'address' => 'Semarang',
      'mobile_phone' => '628123456787',
      'password' => bcrypt('hehe1234'),
      'role_id' => 2
    ]);

    // Satu user yg nyewa deh wkwkw
    User::create([
      'name' => 'garypeter',
      'email' => 'garypeter@gmail.com',
      'address' => 'Batam',
      'mobile_phone' => '628123456786',
      'password' => bcrypt('hehe1234'),
      'role_id' => 1
    ]);
  }
}
