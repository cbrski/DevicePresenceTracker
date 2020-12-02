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

        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>
        <style>
            @media (min-width: 1200px) {
                html {
                    max-width: 1500px;
                    margin: 0 auto;
                }
            }

            body {
                background-color: white;
            }

            a:hover {
                text-decoration: none;
                color: transparent;
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

            .right {
                float: right;
            }

            h3, .h3 {
                line-height: 1;
                margin-bottom: 0;
            }

            .wifi-stale {
                background: rgb(40,167,69);
                background: linear-gradient(90deg, rgba(40,167,69,1) 60%, rgba(255,193,7,1) 90%);
            }
        </style>
    </head>
    <body>
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 row-own">

            @foreach($devices as $key => $device)
                <div class="col mb-3 card-own">
                    <a data-toggle="collapse" href="#device{{ $key }}" role="button" aria-expanded="false" aria-controls="device{{ $key }}">

                        <div class="card
                        @if(strstr($device['lastUsedLink']['dev'], 'wlan'))
                            bg-@include('color-wifi-tracker', ['state' => $device['lastUsedLink']['state']])
                        @else
                            bg-@include('color-main-tracker', ['state' => $device['lastUsedLink']['state']])
                        @endif
                        ">

                        <div class="card-header font-bold">
                            <h3>
                                <span class="badge badge-light">
                                    {{ $device['deviceName'] }}
                                </span>
                                <span class="badge
                                badge-@include('color-main-tracker', ['state' => $device['lastUsedLink']['state']])
                                right">
                                    @include('timestamp-ago-tracker', ['timestamp' => $device['lastUsedLink']['timestamp']])
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
                                        badge-@include('color-intuitive-tracker', ['state' => $device['lastUsedLink']['state']])
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
