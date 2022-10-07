<?php

namespace Database\Factories;

use App\Models\CarDescription;
use Faker\Provider\Fakecar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CarDescription>
 */
class CarDescriptionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CarDescription::class;

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
            'car_model_year' => $this->faker->biasedNumberBetween(1990, date('Y'), 'sqrt'),
            'color' => fake()->colorName(),
            'capacity' => $this->faker->vehicleSeatCount,
            'no_plate' => $this->faker->unique()->bothify('#######'),
        ];
    }
}
