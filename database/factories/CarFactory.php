<?php

namespace Database\Factories;

use App\Models\Car;
use App\Models\User;
use Faker\Provider\FakeCar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Car>
 */
class CarFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Car::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $this->faker->addProvider(new Fakecar($this->faker));

        return [
            'brand_car' => $this->faker->vehicle,
            'owner_id' => User::factory()->create()->id,
            'status_id' => fake()->numberBetween(1, 2),
        ];
    }
}
