<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800|merriweather:400,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-900">
        <div class="flex min-h-screen items-center justify-center px-4 py-8 sm:px-6 lg:px-8">
            <div class="w-full max-w-md rounded-[24px] border border-blue-100 bg-white px-6 py-8 shadow-[0_24px_60px_-34px_rgba(37,99,235,0.35)] sm:px-8">
                <div class="mb-8 flex flex-col items-center text-center">
                    <span class="flex h-16 w-16 items-center justify-center rounded-[22px] bg-gradient-to-br from-blue-700 to-indigo-800 text-lg font-bold tracking-[0.18em] text-white shadow-[0_20px_36px_-20px_rgba(37,99,235,0.75)]">TS</span>
                    <p class="mt-4 text-xs font-semibold uppercase tracking-[0.32em] text-blue-700">Acceso al sistema</p>
                    <h1 class="mt-2 text-2xl font-extrabold text-slate-900">{{ config('app.name', 'Tienda') }}</h1>
                </div>

                {{ $slot }}
            </div>
        </div>
    </body>
</html>
