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
            'order_id' => '#' . $this->faker->randomNumber(8) . time(),
            'account_no' => $this->faker->bankAccountNumber,
            'account_name' => $this->faker->name,
            'amount' => $this->faker->randomNumber(5),
            'callback_url' => $this->faker->url,
            'reference_no' => $this->faker->numerify('N-########'),
        ];
    }
}
