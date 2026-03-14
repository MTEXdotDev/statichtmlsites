<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $page->name }} — File Manager</title>
    @vite(['resources/css/app.css'])
    @livewireStyles

    {{-- CodeMirror 6 via ESM import map (no bundler required for editor) --}}
    <script type="importmap">
    {
        "imports": {
            "@codemirror/state":        "https://esm.sh/@codemirror/state@6",
            "@codemirror/view":         "https://esm.sh/@codemirror/view@6",
            "@codemirror/commands":     "https://esm.sh/@codemirror/commands@6",
            "@codemirror/language":     "https://esm.sh/@codemirror/language@6",
            "@codemirror/lang-html":    "https://esm.sh/@codemirror/lang-html@6",
            "@codemirror/lang-css":     "https://esm.sh/@codemirror/lang-css@6",
            "@codemirror/lang-javascript": "https://esm.sh/@codemirror/lang-javascript@6",
            "@codemirror/lang-json":    "https://esm.sh/@codemirror/lang-json@6",
            "@codemirror/lang-xml":     "https://esm.sh/@codemirror/lang-xml@6",
            "@codemirror/theme-one-dark": "https://esm.sh/@codemirror/theme-one-dark@6"
        }
    }
    </script>
</head>
<body class="h-full bg-[#1e1e2e] text-gray-100 overflow-hidden">

    <livewire:file-manager :slug="$page->slug" />

    @livewireScripts
</body>
</html>
