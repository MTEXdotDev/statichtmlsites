@props(['title' => config('app.name')])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('head')
</head>
<body class="h-full font-sans antialiased text-gray-900" x-data>

<nav class="bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
    <a href="{{ route('dashboard') }}" class="font-bold text-lg tracking-tight text-indigo-600">
        {{ config('app.name') }}
    </a>
    <div class="flex items-center gap-5 text-sm">
        <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-900 transition">
            Dashboard
        </a>
        <a href="{{ route('pages.create') }}"
           class="bg-indigo-600 text-white px-3 py-1.5 rounded-lg hover:bg-indigo-700 transition font-medium">
            + New Page
        </a>
        <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf
            <button type="submit" class="text-gray-400 hover:text-gray-700 transition">
                Logout
            </button>
        </form>
    </div>
</nav>

{{-- Flash messages --}}
@if (session('success'))
    <div x-data="{ show: true }" x-show="show" x-transition
         x-init="setTimeout(() => show = false, 4000)"
         class="bg-green-50 border-l-4 border-green-500 text-green-800 px-6 py-3 text-sm flex justify-between">
        <span>{{ session('success') }}</span>
        <button @click="show = false" class="text-green-500 hover:text-green-800 ml-4">✕</button>
    </div>
@endif

@if ($errors->any())
    <div class="bg-red-50 border-l-4 border-red-500 text-red-800 px-6 py-3 text-sm">
        <ul class="list-disc list-inside space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<main>{{ $slot }}</main>

@livewireScripts
@stack('scripts')
</body>
</html>
