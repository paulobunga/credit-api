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
            'order_id' => '#' . $this->faker->randomNumber(8) . time(),
            'amount' => $this->faker->randomNumber(5),
            'status' => $this->faker->boolean,
        ];
    }
}
