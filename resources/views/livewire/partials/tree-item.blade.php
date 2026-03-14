{{--
  Recursive file-tree item.
  Variables: $item (array), $depth (int, default 0)
  Alpine context: fileManager() component on ancestor div.
--}}
@php
$depth ??= 0;
$pl     = 8 + $depth * 16;

// File-type icon + colour mapping (Flux/Heroicons)
$ext      = strtolower(pathinfo($item['name'], PATHINFO_EXTENSION));
$iconMap  = [
    'html'  => ['name' => 'globe-alt',      'class' => 'text-orange-400'],
    'htm'   => ['name' => 'globe-alt',      'class' => 'text-orange-400'],
    'css'   => ['name' => 'swatch',         'class' => 'text-sky-400'],
    'js'    => ['name' => 'bolt',           'class' => 'text-yellow-400'],
    'json'  => ['name' => 'document-text',  'class' => 'text-amber-500'],
    'md'    => ['name' => 'document-text',  'class' => 'text-zinc-400'],
    'txt'   => ['name' => 'document-text',  'class' => 'text-zinc-400'],
    'xml'   => ['name' => 'code-bracket',   'class' => 'text-green-400'],
    'svg'   => ['name' => 'swatch',         'class' => 'text-pink-400'],
    'png'   => ['name' => 'photo',          'class' => 'text-violet-400'],
    'jpg'   => ['name' => 'photo',          'class' => 'text-violet-400'],
    'jpeg'  => ['name' => 'photo',          'class' => 'text-violet-400'],
    'gif'   => ['name' => 'photo',          'class' => 'text-violet-400'],
    'webp'  => ['name' => 'photo',          'class' => 'text-violet-400'],
    'ico'   => ['name' => 'photo',          'class' => 'text-violet-400'],
    'mp4'   => ['name' => 'film',           'class' => 'text-red-400'],
    'webm'  => ['name' => 'film',           'class' => 'text-red-400'],
    'mp3'   => ['name' => 'musical-note',   'class' => 'text-emerald-400'],
    'wav'   => ['name' => 'musical-note',   'class' => 'text-emerald-400'],
    'ogg'   => ['name' => 'musical-note',   'class' => 'text-emerald-400'],
    'pdf'   => ['name' => 'document',       'class' => 'text-red-500'],
    'woff'  => ['name' => 'document',       'class' => 'text-zinc-500'],
    'woff2' => ['name' => 'document',       'class' => 'text-zinc-500'],
];
$fileIcon = $item['isIndex'] ?? false
    ? ['name' => 'home',     'class' => 'text-emerald-400']
    : ($iconMap[$ext] ?? ['name' => 'document', 'class' => 'text-zinc-500']);
@endphp

@if ($item['type'] === 'dir')
{{-- ── DIRECTORY ──────────────────────────────────────────────────────── --}}
<div>
    <div
        class="flex items-center gap-1.5 py-[3px] pr-1 rounded-md cursor-pointer group
               text-zinc-400 hover:text-zinc-100 hover:bg-white/5 select-none"
        style="padding-left: {{ $pl }}px"
        x-on:click="toggleDir(@js($item['path']))"
        x-on:contextmenu.prevent="openContext($event, @js($item))"
        wire:key="tree-dir-{{ $item['path'] }}"
    >
        {{-- Chevron --}}
        <span class="shrink-0 transition-transform duration-150"
              x-bind:class="isOpen(@js($item['path'])) ? 'rotate-90' : ''">
            <flux:icon.chevron-right class="size-3 text-zinc-600" />
        </span>

        {{-- Folder icon --}}
        <span class="shrink-0">
            <flux:icon.folder-open class="size-4 text-sky-400"
                x-show="isOpen(@js($item['path']))" style="display:none" />
            <flux:icon.folder class="size-4 text-sky-400"
                x-show="!isOpen(@js($item['path']))" />
        </span>

        {{-- Name --}}
        <span class="flex-1 text-xs truncate">{{ $item['name'] }}</span>

        {{-- Hover actions --}}
        <div class="hidden group-hover:flex items-center gap-0.5 ml-1">
            <button
                wire:click.stop="prepareRename(@js($item['path']))"
                class="p-0.5 rounded hover:bg-white/10 text-zinc-500 hover:text-zinc-200 transition"
                title="Rename">
                <flux:icon.pencil class="size-3.5" />
            </button>
            <button
                wire:click.stop="prepareDelete(@js($item['path']), 'dir')"
                class="p-0.5 rounded hover:bg-red-500/20 text-zinc-500 hover:text-red-400 transition"
                title="Delete folder">
                <flux:icon.trash class="size-3.5" />
            </button>
        </div>
    </div>

    {{-- Children --}}
    <div x-show="isOpen(@js($item['path']))" x-cloak>
        @forelse ($item['children'] ?? [] as $child)
            @include('livewire.partials.tree-item', ['item' => $child, 'depth' => $depth + 1])
        @empty
            <p class="text-[11px] text-zinc-600 italic py-1 select-none"
               style="padding-left: {{ $pl + 24 }}px">
                Empty
            </p>
        @endforelse
    </div>
</div>

@else
{{-- ── FILE ────────────────────────────────────────────────────────────── --}}
<div
    class="flex items-center gap-1.5 py-[3px] pr-1 rounded-md cursor-pointer group select-none transition-colors
           {{ ($item['active'] ?? false) ? 'bg-white/10 text-white' : 'text-zinc-400 hover:text-zinc-100 hover:bg-white/5' }}"
    style="padding-left: {{ $pl }}px"
    wire:click="openFile(@js($item['path']))"
    x-on:contextmenu.prevent="openContext($event, @js($item))"
    wire:key="tree-file-{{ $item['path'] }}"
>
    {{-- File icon --}}
    <span class="shrink-0 {{ $fileIcon['class'] }}">
        <flux:icon :name="$fileIcon['name']" class="size-4" />
    </span>

    {{-- Name --}}
    <span class="flex-1 text-xs truncate {{ ($item['isIndex'] ?? false) ? 'font-medium text-emerald-300' : '' }}">
        {{ $item['name'] }}
    </span>

    {{-- Hover actions --}}
    <div class="hidden group-hover:flex items-center gap-0.5 ml-1">
        <button
            wire:click.stop="prepareRename(@js($item['path']))"
            class="p-0.5 rounded hover:bg-white/10 text-zinc-500 hover:text-zinc-200 transition"
            title="Rename">
            <flux:icon.pencil class="size-3.5" />
        </button>
        <button
            wire:click.stop="prepareDelete(@js($item['path']), 'file')"
            class="p-0.5 rounded hover:bg-red-500/20 text-zinc-500 hover:text-red-400 transition"
            title="Delete">
            <flux:icon.trash class="size-3.5" />
        </button>
    </div>
</div>
@endif
