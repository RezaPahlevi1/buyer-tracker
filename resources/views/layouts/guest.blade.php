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
    <body class="font-sans text-[#2B2926] antialiased">
        @php($appTitle = \App\Models\Setting::get('app_title', config('app.name', 'Laravel')))
        <div class="min-h-screen flex flex-col items-center justify-center bg-[#FAF9F6] px-4 py-10">
            <div class="w-full max-w-sm">
                <div class="text-center mb-8">
                    <h1 class="font-serif text-3xl font-semibold text-[#2B2926] tracking-tight">
                        {{ $appTitle }}
                    </h1>
                    <div class="mt-3 mx-auto w-16" aria-hidden="true">
                        <div class="h-[2px] bg-[#2B2926]"></div>
                        <div class="h-[2px] bg-[#2B2926] mt-[3px]"></div>
                    </div>
                    <p class="mt-3 text-[11px] font-semibold tracking-[0.2em] text-[#6B6560] uppercase">
                        Masuk ke Sistem
                    </p>
                </div>

                <div class="bg-white border border-[#D8D3CA] rounded-lg px-8 py-8">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>