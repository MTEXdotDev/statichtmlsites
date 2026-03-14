<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} — Host your static pages instantly</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-full bg-zinc-950 text-white font-sans antialiased">

    <div class="flex min-h-full flex-col items-center justify-center px-6 py-20 text-center">

        <h1 class="text-5xl font-bold mb-4 bg-linear-to-r from-indigo-400 to-purple-400
                   bg-clip-text text-transparent">
            {{ config('app.name') }}
        </h1>

        <p class="text-zinc-400 text-lg mb-10 max-w-md">
            Create and host static HTML pages instantly.<br>
            Each page gets its own subdomain and path URL.
        </p>

        <div class="flex gap-4 justify-center">
            <flux:button href="{{ route('register') }}" variant="primary" size="lg">
                Get Started — Free
            </flux:button>
            <flux:button href="{{ route('login') }}" variant="ghost" size="lg">
                Log In
            </flux:button>
        </div>

        <div class="mt-20 grid grid-cols-1 sm:grid-cols-3 gap-5 max-w-2xl w-full text-left">
            @foreach ([
                ['⚡', 'Instant Deploy', 'Edit files in-browser — changes go live immediately.'],
                ['🌐', 'Dual URLs',      'Subdomain + path-based access. Choose your style.'],
                ['🗂',  'File Manager',  'Upload images, videos, code. Manage everything visually.'],
            ] as [$icon, $title, $desc])
            <div class="bg-zinc-900 rounded-xl p-5 border border-zinc-800">
                <div class="text-2xl mb-2">{{ $icon }}</div>
                <flux:heading size="sm" class="text-white mb-1">{{ $title }}</flux:heading>
                <p class="text-sm text-zinc-400">{{ $desc }}</p>
            </div>
            @endforeach
        </div>

    </div>

</body>
</html>
