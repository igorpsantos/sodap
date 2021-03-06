<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SODAP') }}</title>
        <!-- Favicon -->
        {{-- <link href="{{ asset('argon') }}/img/brand/favicon.png" rel="icon" type="image/png"> --}}
        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">
        <!-- Extra details for Live View on GitHub Pages -->

        <!-- Icons -->
        <link href="{{ asset('argon') }}/vendor/nucleo/css/nucleo.css" rel="stylesheet">
        <link href="{{ asset('argon') }}/vendor/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet">
        <!-- Argon CSS -->
        <link type="text/css" href="{{ asset('argon') }}/css/argon.css?v=1.0.0" rel="stylesheet">
        <link href="/assets/vendor/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet">
    </head>
    <body class="{{ $class ?? '' }}">
        
        @if (Route::currentRouteName() != 'welcome')
            @include('layouts.navbars.sidebar')            
        @endif
        
        <div class="main-content">
            @if (Route::currentRouteName() == 'welcome')
                @include('layouts.navbars.navs.guest')
            @else
                @include('layouts.navbars.navbar')
            @endif
            @yield('content')
        </div>
        @if (Route::currentRouteName() != 'welcome')
            @include('layouts.footers.guest')
        @endif

        <script src="{{ asset('argon') }}/vendor/jquery/dist/jquery.min.js"></script>
        <script src="{{ asset('argon') }}/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
        
        @stack('js')
        
        <!-- Argon JS -->
        <script src="{{ asset('argon') }}/js/argon.js?v=1.0.0"></script>

        <!-- Chart Js 3.4 -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    </body>
</html>