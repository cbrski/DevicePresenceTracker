<?php

namespace Database\Factories;

use App\Log;
use Illuminate\Database\Eloquent\Factories\Factory;

class LogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Log::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'timestamp' => $this->faker->unixTime(),
            'message' =>$this->faker->sentence(),
        ];
    }
}
