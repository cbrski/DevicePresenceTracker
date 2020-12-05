<?php

namespace Database\Factories;

use App\Device;
use App\DeviceLink;
use App\DeviceLinkStateLog;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeviceLinkStateLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DeviceLinkStateLog::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'device_id' => Device::factory(),
            'device_link_id' => DeviceLink::factory(),
            'timestamp' => function() {
                return time()-rand(0,86400);
            },
            'state' => $this->faker->randomElement([
                DeviceLinkStateLog::STATE_PERMAMENT,
                DeviceLinkStateLog::STATE_NOARP,
                DeviceLinkStateLog::STATE_REACHABLE,
                DeviceLinkStateLog::STATE_STALE,
                DeviceLinkStateLog::STATE_NONE,
                DeviceLinkStateLog::STATE_INCOMPLETE,
                DeviceLinkStateLog::STATE_DELAY,
                DeviceLinkStateLog::STATE_PROBE,
                DeviceLinkStateLog::STATE_FAILED,
                DeviceLinkStateLog::STATE_OFFLINE,
            ]),
        ];
    }
}
