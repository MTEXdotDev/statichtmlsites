<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $page->name }} — File Manager</title>
    @vite(['resources/css/app.css', 'resources/js/file-manager.js'])
    @livewireStyles
    <style>
        /* Thin scrollbar for file tree */
        .scrollbar-thin { scrollbar-width: thin; scrollbar-color: rgba(255,255,255,0.1) transparent; }
        .scrollbar-thin::-webkit-scrollbar { width: 4px; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 2px; }
        /* Hide Alpine x-cloak */
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full overflow-hidden bg-zinc-950">

    <livewire:file-manager :slug="$page->slug" />

    @livewireScripts
</body>
</html>
