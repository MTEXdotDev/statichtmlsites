<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $page->name }} — File Manager</title>
    {{-- CSS only in head --}}
    @vite(['resources/css/app.css'])
    @livewireStyles
    <style>
        .scrollbar-thin { scrollbar-width: thin; scrollbar-color: rgba(255,255,255,.1) transparent; }
        .scrollbar-thin::-webkit-scrollbar { width: 4px; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: rgba(255,255,255,.1); border-radius: 2px; }
        [x-cloak] { display: none !important; }
        .drag-over { outline: 2px solid #6366f1; background: rgba(99,102,241,.08); border-radius: .375rem; }
    </style>
</head>
<body class="h-full overflow-hidden bg-zinc-950">

    <livewire:file-manager :slug="$page->slug" />

    {{-- Livewire boots Alpine + Flux's Alpine plugins. Must come first. --}}
    @livewireScripts

    {{-- file-manager.js loaded AFTER @livewireScripts so alpine:init fires correctly --}}
    @vite(['resources/js/file-manager.js'])
</body>
</html>
