<?php

namespace Database\Factories;

use App\Models\Reseller;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResellerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Reseller::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->freeEmail,
            'password' => $this->faker->password,
            'phone' => $this->faker->phoneNumber,
            'credit' => $this->faker->numberBetween(1, 1000),
            'coin' => $this->faker->numberBetween(1, 1000),
            'transaction_fee' => $this->faker->randomFloat(4, 0, 1),
            'pending_limit' => rand(1, 5),
            'status' => $this->faker->boolean,
        ];
    }
}
