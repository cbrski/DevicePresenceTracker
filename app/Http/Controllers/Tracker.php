<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Tracker extends Controller
{
    public function show()
    {
        $ala = null;

        return view('tracker', ['devices' =>
            [
                [
                    'deviceName' => 'laptop1',
                    'lastUsedLink' => [
                        'state' => 'reachable',
                        'timestamp' => time(),
                    ],
                    'links' => [
                        [
                            'lladdr' => '11:22:33:11:22:33',
                            'dev' => 'eth0',
                            'ip' => '10.10.0.1',
                            'hostname' => null,
                            'state' => 'reachable',
                            'timestamp' => '1605565669',
                        ]
                    ]
                ],
                [
                    'deviceName' => 'laptop2',
                    'lastUsedLink' => [
                        'state' => 'stale',
                        'timestamp' => time()-3432,
                    ],
                    'links' => [
                        [
                            'lladdr' => '11:22:33:11:22:33',
                            'dev' => 'eth1.1',
                            'ip' => '192.168.100.100',
                            'hostname' => 'computer',
                            'state' => 'stale',
                            'timestamp' => '1605535669',
                        ],
                        [
                            'lladdr' => '11:22:33:11:22:33',
                            'dev' => 'wlan0',
                            'ip' => '192.168.200.100',
                            'hostname' => null,
                            'state' => 'failed',
                            'timestamp' => '1605435669',
                        ],
                    ]
                ],
            ]
        ]);
    }
}
