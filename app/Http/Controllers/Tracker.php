<?php

namespace App\Http\Controllers;

use App\StorageBroker\DeviceStateOutput;

class Tracker extends Controller
{
    public function show()
    {
        $deviceStateOutput = new DeviceStateOutput();
        $devices = $deviceStateOutput->get();
        return view('tracker', ['devices' => $devices]);
    }
}
