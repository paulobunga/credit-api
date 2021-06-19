<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MerchantDepositFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Models\MerchantDeposit::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'merchant_order_id' => $this->faker->uuid,
            'order_id' => $this->faker->uuid,
            'amount' => $this->faker->randomNumber(5),
            'status' => $this->faker->boolean,
            'callback_url' => $this->faker->url,
            'reference_no' => $this->faker->numerify('N-########')
        ];
    }
}
