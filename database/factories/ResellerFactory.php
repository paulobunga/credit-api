<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ResellerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Models\Reseller::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'username' => $this->faker->numerify('reseller##@gmail.com'),
            'password' => 'P@ssw0rd',
            'phone' => $this->faker->phoneNumber,
            'credit' => 0,
            'coin' => 0,
            'pending_limit' => 0,
            'downline_slot' => 0,
            'status' => $this->faker->numberBetween(0, 2),
        ];
    }
}
