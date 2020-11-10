<?php

namespace Database\Factories;

use App\DeviceMac;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeviceMacFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DeviceMac::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'device_id' => '',
            'mac' => '',
            'link_layer' => '',
        ];
    }
}
