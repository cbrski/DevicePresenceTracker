<?php

namespace Database\Factories;

use App\Device;
use App\DeviceLink;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeviceLinkFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DeviceLink::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'device_id' => Device::factory(),
            'lladdr' => $this->faker->macAddress,
            'dev' => function() {
                $s = ['eth', 'wlan'];
                return $s[rand(0,1)].rand(0,9);
            },
            'ipv4' => $this->faker->ipv4,
            'ipv6' => $this->faker->ipv6,
            'hostname' => $this->faker->colorName
        ];
    }
}
