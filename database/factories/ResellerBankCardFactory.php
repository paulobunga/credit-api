<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ResellerBankCardFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Models\ResellerBankCard::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'type' => $this->faker->numberBetween(0, 2),
            'account_no' => $this->faker->unique()->bankAccountNumber,
            'account_name' => $this->faker->name,
            'status' => $this->faker->boolean,
        ];
    }
}
