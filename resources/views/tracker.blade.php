<!doctype html>
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">

        <title>Device Presence Tracker</title>
    </head>
    <body>
        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>

        <div class="row row-cols-1 row-cols-md-4" style="margin: 8px;">

            @foreach($devices as $device)
                <div class="col mb-4">

                        @if(strstr($device['lastUsedLink']['dev'], 'wlan'))
                            @switch($device['lastUsedLink']['state'])
                                @case('reachable')
                                        <div class="card bg-success">
                                        @break
                                @case('stale')
                                        <div class="card bg-warning">
                                        @break
                                @case('delay')
                                        <div class="card bg-warning">
                                        @break
                                @case('failed')
                                        <div class="card bg-danger">
                                        @break
                                @default
                                        <div class="card bg-secondary">
                            @endswitch
                        @else
                            @switch($device['lastUsedLink']['state'])
                                @case('reachable')
                                        <div class="card bg-success">
                                        @break
                                @case('stale')
                                        <div class="card bg-success">
                                        @break
                                @case('delay')
                                        <div class="card bg-success">
                                        @break
                                @case('failed')
                                        <div class="card bg-danger">
                                        @break
                                @default
                                        <div class="card bg-secondary">
                            @endswitch
                        @endif

                        <div class="card-header" style="font-weight: bold">
                            <h3>
                                <span class="badge badge-light">
                                    {{ $device['deviceName'] }}
                                </span>
                                <span class="badge badge-secondary">
                                    @php
                                        echo date("H:i", $device['lastUsedLink']['timestamp'])
                                    @endphp
                                </span>
                            </h3>
                        </div>

                        <div class="card-body">

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

                                            @switch($link['state'])
                                                @case('reachable')
                                                        <span class="badge badge-success">
                                                        @break
                                                @case('stale')
                                                        <span class="badge badge-warning">
                                                        @break
                                                @case('delay')
                                                        <span class="badge badge-warning">
                                                        @break
                                                @case('failed')
                                                        <span class="badge badge-danger">
                                                        @break
                                                @default
                                                        <div class="badge badge-secondary">
                                                @endswitch
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
