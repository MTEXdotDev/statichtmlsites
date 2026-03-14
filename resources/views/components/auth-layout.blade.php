@props(['title' => config('app.name')])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-white dark:bg-zinc-900 font-sans antialiased">

    <div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <a href="{{ route('home') }}" class="flex justify-center">
                <flux:heading size="xl" class="text-accent font-bold tracking-tight">
                    {{ config('app.name') }}
                </flux:heading>
            </a>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-sm">
            {{ $slot }}
        </div>
    </div>

</body>
</html>
