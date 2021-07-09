<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MerchantFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Models\Merchant::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'username' => $this->faker->unique()->userName,
            'phone' => $this->faker->phoneNumber,
            'credit' => $this->faker->numberBetween(1, 1000),
            'transaction_fee' => $this->faker->randomFloat(4, 0, 0.03),
            'password' => $this->faker->password,
            'callback_url' => $this->faker->url,
            'status' => $this->faker->boolean,
        ];
    }
}
