<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ReportMonthlyMerchantFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Models\ReportMonthlyMerchant::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'turnover' => $this->faker->randomNumber(3),
            'payin' => $this->faker->randomNumber(5),
            'payout' => $this->faker->randomNumber(5),
        ];
    }
}
