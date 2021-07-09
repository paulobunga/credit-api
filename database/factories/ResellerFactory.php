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
            'username' => $this->faker->unique()->freeEmail,
            'password' => $this->faker->password,
            'phone' => $this->faker->phoneNumber,
            'credit' => $this->faker->numberBetween(1, 1000),
            'coin' => $this->faker->numberBetween(1, 1000),
            'transaction_fee' => $this->faker->randomFloat(4, 0, 0.03),
            'pending_limit' => $this->faker->numberBetween(1, 5),
            'status' => $this->faker->boolean,
        ];
    }
}
