<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'CarSafe Driver') }}</title>

    <!-- PWA Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#7366ff">

    <!-- Apple iOS Safari Meta Tags -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="CarSafe Driver">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/logo/carsafe-icon-192.png') }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('assets/images/logo/carsafe-icon-192.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/images/logo/carsafe-icon-192.png') }}">
    <link rel="apple-touch-icon" sizes="167x167" href="{{ asset('assets/images/logo/carsafe-icon-192.png') }}">

    <link rel="icon" href="{{ asset('assets/images/logo/carsafe-icon.png') }}" type="image/x-icon">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('assets/css/vendors/bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    @yield('css')
</head>
<body>
    <div id="app">
        @yield('content')
    </div>

    <!-- Scripts -->
    <script src="{{ asset('assets/js/jquery-3.5.1.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap/bootstrap.bundle.min.js') }}"></script>
    @yield('js')

    <!-- Driver PWA Service Worker & iOS Custom Banner -->
    @include('partials.pwa-driver-banner')
</body>
</html>
