<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ \App\Models\Setting::get('app_title', config('app.name', 'Laravel')) }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-800 antialiased">
        @php
            $pageBackgroundType = \App\Models\Setting::get('menu_background_type', 'color');
            $pageBackgroundValue = \App\Models\Setting::get('menu_background_value', '#f3f4f6');
        @endphp
        <div
            class="min-h-screen flex flex-col"
            @if ($pageBackgroundType === 'image' && $pageBackgroundValue)
                style="background-image: url('{{ \Illuminate\Support\Facades\Storage::url($pageBackgroundValue) }}'); background-size: cover; background-position: center;"
            @else
                style="background-color: {{ $pageBackgroundValue }};"
            @endif
        >
            <livewire:layout.topbar />

            <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8 pb-24">
                {{ $slot }}
            </main>

            <livewire:layout.bottombar />
        </div>
    </body>
</html>