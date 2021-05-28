<?php

namespace Database\Factories;

use App\Models\Merchant;
use Illuminate\Database\Eloquent\Factories\Factory;

class MerchantFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Merchant::class;

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
            'password' => $this->faker->password,
            'api_whitelist' => [
                $this->faker->ipv4,
                $this->faker->ipv4,
            ],
            'callback_url' => $this->faker->url
        ];
    }
}
