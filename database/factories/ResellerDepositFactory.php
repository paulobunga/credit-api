<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ResellerDepositFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Models\ResellerDeposit::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'order_id' => $this->faker->uuid,
            'amount' => $this->faker->randomNumber(5),
            'status' => $this->faker->boolean,
            'reference_no' => $this->faker->numerify('N-########')
        ];
    }
}
