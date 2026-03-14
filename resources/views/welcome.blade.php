<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} — Host static pages instantly</title>
    <script>
        (function () {
            const t = localStorage.getItem('theme');
            if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-full bg-white dark:bg-zinc-950 text-zinc-800 dark:text-zinc-100
             font-sans antialiased transition-colors">

    {{-- Nav --}}
    <nav class="border-b border-zinc-200 dark:border-zinc-800 px-6 py-3 flex items-center justify-between">
        <span class="font-bold text-brand-600 dark:text-brand-400 tracking-tight">
            {{ config('app.name') }}
        </span>
        <div class="flex items-center gap-3">
            <x-theme-toggle />
            <a href="{{ route('login') }}"
               class="text-sm text-zinc-500 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-100 transition">
                Log In
            </a>
            <a href="{{ route('register') }}"
               class="text-sm font-medium px-4 py-2 rounded-lg
                      bg-brand-600 hover:bg-brand-500 text-white transition">
                Get Started
            </a>
        </div>
    </nav>

    {{-- Hero --}}
    <div class="flex flex-col items-center justify-center text-center px-6 py-24">
        <div class="inline-flex items-center gap-2 text-xs font-medium px-3 py-1.5 rounded-full mb-6
                    bg-brand-50 dark:bg-brand-950/50 text-brand-600 dark:text-brand-400
                    border border-brand-200 dark:border-brand-800">
            <span class="size-1.5 rounded-full bg-brand-500 animate-pulse"></span>
            Now in beta
        </div>

        <h1 class="text-5xl sm:text-6xl font-bold tracking-tight mb-5 max-w-2xl
                   bg-gradient-to-br from-zinc-900 to-zinc-600 dark:from-white dark:to-zinc-400
                   bg-clip-text text-transparent">
            Host static HTML pages instantly
        </h1>

        <p class="text-lg text-zinc-500 dark:text-zinc-400 mb-10 max-w-lg leading-relaxed">
            Create and manage static mini-sites with a built-in code editor.
            Every page gets its own subdomain and path URL — live in seconds.
        </p>

        <div class="flex flex-wrap gap-3 justify-center">
            <a href="{{ route('register') }}"
               class="px-6 py-3 text-sm font-semibold rounded-xl
                      bg-brand-600 hover:bg-brand-500 text-white transition shadow-sm">
                Get Started — Free
            </a>
            <a href="{{ route('login') }}"
               class="px-6 py-3 text-sm font-semibold rounded-xl
                      bg-zinc-100 dark:bg-white/8 hover:bg-zinc-200 dark:hover:bg-white/14
                      text-zinc-700 dark:text-zinc-300 transition">
                Log In
            </a>
        </div>
    </div>

    {{-- Feature cards --}}
    <div class="max-w-4xl mx-auto px-6 pb-24 grid grid-cols-1 sm:grid-cols-3 gap-5">
        @foreach ([
            ['⚡', 'Instant Deploy',  'Edit in the browser — changes go live immediately.'],
            ['🌐', 'Dual URLs',       'Subdomain + path-based access. Both always work.'],
            ['🗂',  'Full File Manager', 'Upload images, video, code. Manage everything visually.'],
        ] as [$icon, $title, $desc])
        <div class="p-5 rounded-2xl border border-zinc-200 dark:border-zinc-800
                    bg-zinc-50 dark:bg-zinc-900 hover:border-brand-300 dark:hover:border-brand-700
                    transition group">
            <div class="text-2xl mb-3">{{ $icon }}</div>
            <h3 class="text-sm font-semibold text-zinc-900 dark:text-white mb-1.5
                       group-hover:text-brand-600 dark:group-hover:text-brand-400 transition">
                {{ $title }}
            </h3>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $desc }}</p>
        </div>
        @endforeach
    </div>

    @livewireScripts
</body>
</html>
