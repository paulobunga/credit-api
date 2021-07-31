<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ResellerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Models\Reseller::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $level = [
            ['level' => 0, 'commission_percentage' => 0], # referrer
            ['level' => 1, 'commission_percentage' => 0.003], # master agent
            ['level' => 2, 'commission_percentage' => 0.004], # agent
            ['level' => 3, 'commission_percentage' => 0.005], # reseller
        ];
        shuffle($level);
        
        return [
            'name' => $this->faker->name,
            'username' => $this->faker->numerify('reseller##@gmail.com'),
            'password' => 'P@ssw0rd',
            'level' => $level[0]['level'],
            'phone' => $this->faker->phoneNumber,
            'credit' => $this->faker->numberBetween(1, 1000),
            'coin' => $this->faker->numberBetween(1, 1000),
            'pending_limit' => 0,
            'commission_percentage' => 0,
            'downline_slot' => 0,
            'status' => $this->faker->numberBetween(0, 2),
        ];
    }
}
