<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Preview — {{ $page->name }}</title>
    @vite(['resources/css/app.css'])
    <style>
        [x-cloak] { display: none !important; }
        .device-frame { transition: width 0.3s cubic-bezier(.4,0,.2,1), height 0.3s cubic-bezier(.4,0,.2,1); }
    </style>
</head>
<body class="h-full overflow-hidden bg-zinc-950 text-zinc-100 flex flex-col"
      x-data="{
          device: 'desktop',
          url: '{{ $page->subdomainUrl() }}',
          devices: {
              mobile:  { w: '390px',  h: '844px',  label: 'Mobile',  icon: 'device-phone-mobile' },
              tablet:  { w: '768px',  h: '1024px', label: 'Tablet',  icon: 'device-tablet' },
              desktop: { w: '100%',   h: '100%',   label: 'Desktop', icon: 'computer-desktop' },
          },
          get frameStyle() {
              const d = this.devices[this.device];
              return this.device === 'desktop'
                  ? 'width:100%;height:100%'
                  : `width:${d.w};max-height:${d.h};border-radius:12px;box-shadow:0 25px 60px rgba(0,0,0,.6)`;
          },
          reload() {
              const f = document.getElementById('preview-frame');
              f.src = f.src;
          },
          copyUrl() {
              navigator.clipboard.writeText(this.url);
          }
      }">

    {{-- ── Toolbar ─────────────────────────────────────────────────────────── --}}
    <header class="h-12 shrink-0 flex items-center gap-2 px-4 border-b border-white/8 bg-zinc-900/80 backdrop-blur">

        {{-- Back --}}
        <a href="{{ route('pages.manager', $page->slug) }}"
           class="flex items-center gap-1.5 text-xs text-zinc-400 hover:text-zinc-100 transition
                  px-2 py-1.5 rounded-md hover:bg-white/8 shrink-0">
            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
            </svg>
            Editor
        </a>

        <div class="w-px h-5 bg-white/10 shrink-0"></div>

        {{-- URL bar --}}
        <div class="flex-1 flex items-center gap-2 bg-white/6 border border-white/10 rounded-lg px-3 py-1.5
                    max-w-xl mx-auto">
            <svg class="size-3.5 text-zinc-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5a17.92 17.92 0 0 1-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418"/>
            </svg>
            <span class="flex-1 text-xs text-zinc-300 font-mono truncate" x-text="url"></span>
            <button x-on:click="copyUrl()"
                    class="text-zinc-500 hover:text-zinc-200 transition shrink-0" title="Copy URL">
                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184"/>
                </svg>
            </button>
        </div>

        {{-- Device toggles --}}
        <div class="flex items-center gap-0.5 bg-white/6 border border-white/10 rounded-lg p-0.5 shrink-0">
            <template x-for="[key, d] in Object.entries(devices)" :key="key">
                <button
                    x-on:click="device = key"
                    x-bind:class="device === key
                        ? 'bg-white/14 text-zinc-100'
                        : 'text-zinc-500 hover:text-zinc-300'"
                    x-bind:title="d.label"
                    class="px-2 py-1 rounded-md transition text-xs flex items-center gap-1">
                    {{-- Mobile icon --}}
                    <template x-if="key === 'mobile'">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 8.25h3"/>
                        </svg>
                    </template>
                    {{-- Tablet icon --}}
                    <template x-if="key === 'tablet'">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5h3m-6.75 2.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-15a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 4.5v15a2.25 2.25 0 0 0 2.25 2.25Z"/>
                        </svg>
                    </template>
                    {{-- Desktop icon --}}
                    <template x-if="key === 'desktop'">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0H3"/>
                        </svg>
                    </template>
                    <span class="hidden lg:inline text-[11px]" x-text="d.label"></span>
                </button>
            </template>
        </div>

        {{-- Reload --}}
        <button x-on:click="reload()"
                class="flex items-center gap-1 text-zinc-400 hover:text-zinc-100 transition
                       px-2 py-1.5 rounded-md hover:bg-white/8 shrink-0">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
            </svg>
        </button>

        {{-- Open externally --}}
        <a href="{{ $page->subdomainUrl() }}" target="_blank" rel="noopener"
           class="flex items-center gap-1 text-zinc-400 hover:text-zinc-100 transition
                  px-2 py-1.5 rounded-md hover:bg-white/8 shrink-0" title="Open in new tab">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
            </svg>
        </a>
    </header>

    {{-- ── Preview frame ───────────────────────────────────────────────────── --}}
    <div class="flex-1 flex items-center justify-center overflow-hidden"
         x-bind:class="device !== 'desktop' ? 'p-6 bg-zinc-900/40' : ''">
        <div class="device-frame overflow-hidden"
             x-bind:class="device !== 'desktop' ? 'ring-1 ring-white/10' : 'w-full h-full'"
             x-bind:style="frameStyle">
            <iframe
                id="preview-frame"
                src="{{ $page->subdomainUrl() }}"
                class="w-full h-full border-0 bg-white"
                sandbox="allow-scripts allow-same-origin allow-forms allow-popups"
                title="{{ $page->name }} preview">
            </iframe>
        </div>
    </div>

</body>
</html>
