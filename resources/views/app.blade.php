<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="xsrf-cookie" content="{{ config('session.xsrf_cookie', 'XSRF-TOKEN') }}">
        @php
            $documentTitle = config('platform.control_plane', false)
                ? config('app.name')
                : (config('platform.client_name') ?: config('app.name'));
        @endphp
        <meta name="app-name" content="{{ $documentTitle }}">
        <title>{{ $documentTitle }}</title>
        @vite(['resources/js/app.js'])
        @inertiaHead
    </head>
    <body class="bg-surface-container-low text-on-surface font-inter antialiased">
        @inertia
    </body>
</html>
