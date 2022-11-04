<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        @hasSection('title')
            <title>@yield('title') - {{ config('app.name') }}</title>
        @else
            <title>{{ config('app.name') }}</title>
        @endif
        <meta name="csrf-token" content="{{ csrf_token() }}">
    </head>
    <link rel="shortcut icon" href="{{ url(asset('favicon.ico')) }}">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @livewireStyles
    <body class="antialiased">
        @yield('content')
        @livewireScripts
        @stack('scripts')
    </body>
</html>
