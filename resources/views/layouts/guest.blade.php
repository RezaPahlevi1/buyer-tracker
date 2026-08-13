<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ \App\Models\Setting::get('app_title', config('app.name', 'Laravel')) }}</title>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-800 antialiased">
        @php($appTitle = \App\Models\Setting::get('app_title', config('app.name', 'Laravel')))
        <div class="min-h-screen flex flex-col items-center justify-center bg-slate-50 px-4 py-10">
            <div class="w-full max-w-sm">
                <h1 class="text-center font-semibold text-2xl text-slate-800 mb-8">
                    {{ $appTitle }}
                </h1>

                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="h-1 bg-blue-600"></div>
                    <div class="px-6 pt-6 pb-6">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>