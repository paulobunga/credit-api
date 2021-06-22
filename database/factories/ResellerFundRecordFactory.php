<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ResellerFundRecordFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Models\ResellerFundRecord::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'type' => $this->faker->numberBetween(0, 3),
            'amount' => $this->faker->numberBetween(1, 1000),
            'created_at' => \Carbon\Carbon::now()
        ];
    }
}
