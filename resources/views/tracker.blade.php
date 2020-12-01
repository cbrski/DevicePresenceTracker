<!doctype html>
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta http-equiv="refresh" content="60">

        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">

        <title>Device Presence Tracker</title>

        <style>
            a:hover {
                text-decoration: none;
                color: transparent;
            }

            body {
                background-color: white;
            }

            @media (prefers-color-scheme: light) {
                body {
                    background-color: white;
                }
            }

            @media (prefers-color-scheme: dark) {
                body {
                    background-color: black;
                }
            }

            .card-own {
                padding: 8px;
                margin: 0 !important;
                line-height: 1;
            }

            .row-own {
                margin: 2px;
            }

            .font-bold {
                font-weight: bold
            }

            .padding-margin-0 {
                padding: 0;
                margin: 0;
            }
        </style>
    </head>
    <body>
        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 row-own">

            @foreach($devices as $key => $device)
                <div class="col mb-3 card-own">
                    <a data-toggle="collapse" href="#device{{ $key }}" role="button" aria-expanded="false" aria-controls="device{{ $key }}">

                        <div class="card
                        @if(strstr($device['lastUsedLink']['dev'], 'wlan'))
                            @switch($device['lastUsedLink']['state'])
                                @case('reachable')
                                            bg-success
                                            @break
                                @case('stale')
                                @case('delay')
                                            bg-warning
                                            @break
                                @case('failed')
                                @case(\App\DeviceLinkStateLog::STATE_OFFLINE)
                                            bg-danger
                                            @break
                                @default
                                            bg-secondary
                            @endswitch
                        @else
                            @switch($device['lastUsedLink']['state'])
                                @case('reachable')
                                @case('stale')
                                @case('delay')
                                            bg-success
                                            @break
                                @case('failed')
                                @case(\App\DeviceLinkStateLog::STATE_OFFLINE)
                                            bg-danger
                                            @break
                                @default
                                            bg-secondary
                            @endswitch
                        @endif
                        ">

                        <div class="card-header font-bold">
                            <h3>
                                <span class="badge badge-light">
                                    {{ $device['deviceName'] }}
                                </span>
                                <span class="badge badge-secondary">
                                    @php
                                        $sec_diff = time() - $device['lastUsedLink']['timestamp'];

                                        $r = (int) ($sec_diff/(86400*7));
                                        $sec_diff-=$r*(86400*7);
                                        if (!$r)
                                        {
                                            $r = (int) ($sec_diff/86400);
                                            $sec_diff-=$r*86400;
                                            if (!$r)
                                            {
                                                $r = (int) ($sec_diff/3600);
                                                $sec_diff-=$r*3600;
                                                if (!$r)
                                                {
                                                    $r = (int) ($sec_diff/60);
                                                    $sec_diff-=$r*60;
                                                    if (!$r)
                                                    {
                                                        echo 'now';
                                                    }
                                                    else
                                                    {
                                                        echo $r.'m ago';
                                                    }
                                                }
                                                else
                                                {
                                                    echo $r.'h ago';
                                                }
                                            }
                                            else
                                            {
                                                echo $r.'d ago';
                                            }
                                        }
                                        else
                                        {
                                            echo $r.'w ago';
                                        }
                                    @endphp
                                </span>
                            </h3>
                        </div>
                    </a>
                </div>
                <div class="collapse" id="device{{ $key }}">
                    <div class="card-body padding-margin-0">
                        @foreach($device['links'] as $link)
                            <div class="card mb-3 text-secondary">
                                <div class="card-header">
                                    @if (null != $link['hostname'])
                                        <b>{{ $link['hostname'] }}</b>
                                    @else
                                        <span class="badge badge-secondary">null</span>
                                    @endif
                                </div>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">

                                        <span class="badge
                                        @switch($link['state'])
                                            @case('reachable')
                                                        badge-success
                                                        @break
                                            @case('stale')
                                            @case('delay')
                                                        badge-warning
                                                        @break
                                            @case('failed')
                                            @case(\App\DeviceLinkStateLog::STATE_OFFLINE)
                                                        badge-danger
                                                        @break
                                            @default
                                                        badge-secondary
                                            @endswitch
                                        ">
                                            {{ $link['state'] }}
                                        </span>
                                        <span class="badge badge-light">
                                            @php
                                                echo date('H:i:s d-m-Y', $link['timestamp'])
                                            @endphp
                                        </span>
                                    </li>
                                    <li class="list-group-item">
                                        <span class="badge badge-light">{{ $link['dev'] }}</span>
                                        <span class="badge badge-light">{{ $link['ip'] }}</span>
                                        <span class="badge badge-light">{{ $link['lladdr'] }}</span>
                                    </li>
                                </ul>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </body>
</html>
