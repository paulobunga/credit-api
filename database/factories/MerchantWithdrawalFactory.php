<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MerchantWithdrawalFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Models\MerchantWithdrawal::class;

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
            'password' => '123456',
            'status' => $this->faker->boolean,
        ];
    }
}
