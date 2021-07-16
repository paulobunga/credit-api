<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BankFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Models\Bank::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word . ' bank',
            'ident' => substr($this->faker->unique()->swiftBicNumber, 0, 6),
            'status' => true,
        ];
    }
}
