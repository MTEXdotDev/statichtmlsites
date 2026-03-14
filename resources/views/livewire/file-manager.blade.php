{{--
  File Manager
  - Modals: pure Alpine x-show (no Flux Pro required)
  - Flux used only for free components: icons, inputs, buttons, badges
  - Drag-and-drop upload
--}}
<div
    class="h-screen flex flex-col bg-zinc-950 text-zinc-100 overflow-hidden"
    x-data="fileManager()"
    x-on:keydown.ctrl.s.window.prevent="save()"
    x-on:keydown.meta.s.window.prevent="save()"
    x-on:dragover.window.prevent="dragOver = true"
    x-on:dragleave.window="dragOver = false"
    x-on:drop.window.prevent="onDrop($event)"
>

{{-- ══════════════════════════════════════════════════════════════════════
     TOP BAR
══════════════════════════════════════════════════════════════════════ --}}
<header class="h-11 shrink-0 flex items-center gap-2 px-3 border-b border-white/8 bg-zinc-900/80 backdrop-blur z-10">
    <a href="{{ route('dashboard') }}"
       class="flex items-center gap-1 text-xs text-zinc-500 hover:text-zinc-100
              px-2 py-1 rounded-md hover:bg-white/8 transition shrink-0">
        <flux:icon.arrow-left class="size-3.5" />
        <span>Dashboard</span>
    </a>

    <div class="w-px h-5 bg-white/10 shrink-0"></div>

    <div class="flex items-center gap-2 min-w-0 flex-1">
        <span class="text-sm font-medium text-zinc-200 truncate max-w-xs">{{ $page->name }}</span>
        <code class="text-[11px] bg-white/8 text-indigo-300 px-1.5 py-0.5 rounded font-mono shrink-0">
            {{ $page->slug }}
        </code>
        @if ($page->is_public)
            <span class="hidden sm:flex items-center gap-1 text-[11px] text-emerald-400 shrink-0">
                <flux:icon.globe-alt class="size-3" /> Public
            </span>
        @else
            <span class="hidden sm:flex items-center gap-1 text-[11px] text-zinc-500 shrink-0">
                <flux:icon.lock-closed class="size-3" /> Private
            </span>
        @endif
    </div>

    <span x-show="dirty"
          class="flex items-center gap-1 text-[11px] text-amber-400 shrink-0">
        <span class="size-1.5 rounded-full bg-amber-400 animate-pulse"></span>
        Unsaved
    </span>

    <a href="{{ route('pages.preview', $page->slug) }}" target="_blank"
       class="hidden sm:flex items-center gap-1.5 text-xs px-2.5 py-1.5 rounded-md
              bg-white/8 hover:bg-white/14 text-zinc-300 hover:text-white transition shrink-0">
        <flux:icon.eye class="size-3.5" />
        Preview
    </a>

    <a href="{{ $page->subdomainUrl() }}" target="_blank" rel="noopener"
       class="hidden sm:flex items-center gap-1.5 text-xs px-2.5 py-1.5 rounded-md
              bg-indigo-600 hover:bg-indigo-500 text-white transition shrink-0">
        <flux:icon.arrow-top-right-on-square class="size-3.5" />
        Open Site
    </a>

    <button x-on:click="openModal('settings')"
            class="flex items-center gap-1 p-1.5 rounded-md text-zinc-400
                   hover:text-zinc-100 hover:bg-white/8 transition shrink-0">
        <flux:icon.cog-6-tooth class="size-4.5" />
    </button>
</header>

{{-- ══════════════════════════════════════════════════════════════════════
     MAIN SPLIT
══════════════════════════════════════════════════════════════════════ --}}
<div class="flex flex-1 overflow-hidden min-h-0">

{{-- ── SIDEBAR ──────────────────────────────────────────────────────── --}}
<aside class="w-56 lg:w-60 shrink-0 flex flex-col border-r border-white/8 bg-zinc-900/40 overflow-hidden">

    {{-- Toolbar --}}
    <div class="h-9 flex items-center gap-1 px-2 border-b border-white/8 shrink-0">
        <span class="text-[10px] text-zinc-600 uppercase tracking-wider font-semibold px-1 flex-1">Explorer</span>
        <button x-on:click="openModal('new-file')" title="New File"
                class="p-1 rounded text-zinc-500 hover:text-zinc-100 hover:bg-white/8 transition">
            <flux:icon.document-plus class="size-4" />
        </button>
        <button x-on:click="openModal('new-folder')" title="New Folder"
                class="p-1 rounded text-zinc-500 hover:text-zinc-100 hover:bg-white/8 transition">
            <flux:icon.folder-plus class="size-4" />
        </button>
        <button x-on:click="openModal('upload')" title="Upload"
                class="p-1 rounded text-zinc-500 hover:text-zinc-100 hover:bg-white/8 transition">
            <flux:icon.arrow-up-tray class="size-4" />
        </button>
        <button wire:click="$refresh" title="Refresh"
                class="p-1 rounded text-zinc-500 hover:text-zinc-100 hover:bg-white/8 transition">
            <flux:icon.arrow-path class="size-3.5" />
        </button>
    </div>

    {{-- Breadcrumb --}}
    @if ($currentDir)
    <div class="flex items-center gap-1.5 px-3 py-1 border-b border-white/8 bg-white/3 shrink-0">
        <button wire:click="goUp" class="text-zinc-500 hover:text-zinc-200 transition" title="Up">
            <flux:icon.arrow-uturn-left class="size-3.5" />
        </button>
        <span class="text-[11px] text-zinc-500 font-mono truncate">/ {{ $currentDir }}</span>
    </div>
    @endif

    {{-- File tree --}}
    <div class="flex-1 overflow-y-auto py-1 min-h-0"
         style="scrollbar-width: thin; scrollbar-color: rgba(255,255,255,.1) transparent">
        @forelse ($this->fileTree as $item)
            @include('livewire.partials.tree-item', ['item' => $item, 'depth' => 0])
        @empty
            <div class="flex flex-col items-center justify-center h-32 text-zinc-600">
                <flux:icon.document class="size-7 mb-2 opacity-40" />
                <p class="text-xs mb-2">No files yet</p>
                <button x-on:click="openModal('new-file')"
                        class="text-xs text-indigo-400 hover:text-indigo-300 transition">
                    + Create index.html
                </button>
            </div>
        @endforelse
    </div>

    {{-- Inline rename --}}
    @if ($renameTarget !== null)
    <div class="border-t border-white/8 p-2 bg-zinc-900 shrink-0">
        <p class="text-[11px] text-zinc-500 mb-1 flex items-center gap-1">
            <flux:icon.pencil class="size-3" /> Rename
        </p>
        <input wire:model="renameTo" type="text" autofocus
               wire:keydown.enter="confirmRename"
               wire:keydown.escape="$set('renameTarget', null)"
               class="w-full bg-white/8 border border-white/15 text-zinc-100 text-xs
                      rounded-md px-2 py-1.5 font-mono
                      focus:outline-none focus:ring-1 focus:ring-indigo-500">
        <div class="flex gap-1.5 mt-1.5">
            <button wire:click="confirmRename"
                    class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white text-xs rounded-md py-1 font-medium transition">
                Rename
            </button>
            <button wire:click="$set('renameTarget', null)"
                    class="flex-1 bg-white/8 hover:bg-white/14 text-zinc-300 text-xs rounded-md py-1 transition">
                Cancel
            </button>
        </div>
    </div>
    @endif

    {{-- Drop-zone hint --}}
    <div x-show="dragOver"
         class="border-t border-indigo-500/50 bg-indigo-500/10 px-3 py-2 text-xs
                text-indigo-300 flex items-center gap-2 shrink-0 pointer-events-none">
        <flux:icon.arrow-up-tray class="size-4" />
        Drop to upload
    </div>
</aside>

{{-- ── EDITOR PANE ──────────────────────────────────────────────────── --}}
<div class="flex-1 flex flex-col overflow-hidden min-w-0">

    {{-- Editor toolbar --}}
    <div class="h-9 flex items-center gap-2 px-3 border-b border-white/8 bg-zinc-900/30 shrink-0">
        <flux:icon.document class="size-3.5 text-zinc-600 shrink-0" />
        <span class="text-xs text-zinc-400 font-mono truncate flex-1 min-w-0">
            {{ $activeFile ?: 'No file open' }}
        </span>
        <div class="flex items-center gap-2 shrink-0">
            {{-- Save button — shown when dirty (Alpine), hidden otherwise --}}
            <button x-show="dirty"
                    x-on:click="save()"
                    class="flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-md
                           bg-indigo-600 hover:bg-indigo-500 text-white transition"
                    style="display:none">
                <flux:icon.check class="size-3" />
                Save <kbd class="opacity-50 text-[10px]">⌘S</kbd>
            </button>
            {{-- Saved indicator — shown when file is open, editable, and not dirty --}}
            @if ($activeFile && $this->isEditable)
                <span x-show="!dirty"
                      class="flex items-center gap-1 text-[11px] text-emerald-500">
                    <flux:icon.check-circle class="size-3" /> Saved
                </span>
            @endif
        </div>
    </div>

    {{-- Content --}}
    @if ($this->isEditable && $activeFile)
        {{-- wire:ignore prevents Livewire from ever touching this subtree.
             Without it, any Livewire re-render destroys the CodeMirror DOM. --}}
        <div wire:ignore id="cm-host" class="flex-1 overflow-hidden min-h-0"></div>

    @elseif ($this->isPreviewable && $activeFile)
        @php $ext = strtolower(pathinfo($activeFile, PATHINFO_EXTENSION)); @endphp
        <div class="flex-1 flex items-center justify-center bg-zinc-950/60 p-8 min-h-0 overflow-auto">
            @if (in_array($ext, ['png','jpg','jpeg','gif','webp','ico','svg']))
                <img src="{{ $this->previewUrl }}"
                     class="max-h-full max-w-full object-contain rounded-lg shadow-2xl ring-1 ring-white/10">
            @elseif (in_array($ext, ['mp4','webm']))
                <video src="{{ $this->previewUrl }}" controls class="max-h-full max-w-full rounded-lg shadow-2xl"></video>
            @elseif (in_array($ext, ['mp3','wav','ogg']))
                <div class="text-center">
                    <flux:icon.musical-note class="size-16 text-zinc-600 mx-auto mb-4" />
                    <p class="text-sm text-zinc-400 mb-4">{{ basename($activeFile) }}</p>
                    <audio src="{{ $this->previewUrl }}" controls class="w-80"></audio>
                </div>
            @elseif ($ext === 'pdf')
                <iframe src="{{ $this->previewUrl }}" class="w-full h-full rounded-lg border-0"></iframe>
            @endif
        </div>

    @elseif ($activeFile)
        <div class="flex-1 flex flex-col items-center justify-center text-zinc-600">
            <flux:icon.document class="size-16 mb-3 opacity-30" />
            <p class="text-sm">{{ basename($activeFile) }}</p>
            <p class="text-xs mt-1 opacity-60">Binary file — cannot be edited</p>
        </div>

    @else
        <div class="flex-1 flex flex-col items-center justify-center text-zinc-700">
            <flux:icon.code-bracket-square class="size-20 mb-4 opacity-20" />
            <p class="text-base font-medium mb-1">No file open</p>
            <p class="text-sm opacity-60 mb-4">Select a file or create a new one</p>
            <button x-on:click="openModal('new-file')"
                    class="flex items-center gap-1.5 text-sm text-indigo-400 hover:text-indigo-300 transition">
                <flux:icon.plus class="size-4" /> New file
            </button>
        </div>
    @endif
</div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     STATUS BAR
══════════════════════════════════════════════════════════════════════ --}}
<footer class="h-6 shrink-0 flex items-center justify-between px-3 bg-indigo-950/40
               border-t border-white/8 text-[11px] text-zinc-500 select-none">
    <div class="flex items-center gap-4">
        @if ($activeFile)
            <span class="font-mono">{{ basename($activeFile) }}</span>
            @if ($this->isEditable)
                <span x-data="{ line: 1, col: 1 }"
                      x-on:cm-cursor.window="line = $event.detail.line; col = $event.detail.col"
                      class="tabular-nums">
                    Ln <span x-text="line"></span>, Col <span x-text="col"></span>
                </span>
                <span class="capitalize">{{ $this->editorLanguage }}</span>
            @endif
        @endif
    </div>
    <div class="flex items-center gap-3">
        <span>{{ $page->slug }}</span>
        <span class="{{ $page->is_public ? 'text-emerald-500' : 'text-zinc-600' }}">
            {{ $page->is_public ? '🌐 Public' : '🔒 Private' }}
        </span>
    </div>
</footer>

{{-- ══════════════════════════════════════════════════════════════════════
     CONTEXT MENU
══════════════════════════════════════════════════════════════════════ --}}
<template x-if="ctx.show">
    <div class="fixed z-[200] min-w-44 bg-zinc-800 border border-white/12 rounded-lg
                shadow-2xl py-1 text-sm"
         x-bind:style="`top:${Math.min(ctx.y, innerHeight-180)}px;left:${Math.min(ctx.x, innerWidth-180)}px`"
         x-on:click.stop>

        <template x-if="ctx.item?.type === 'dir'">
            <div>
                <button x-on:click="openModal('new-file'); closeCtx()"
                        class="ctx-item"><flux:icon.document-plus class="size-4 text-zinc-500" /> New File</button>
                <button x-on:click="openModal('new-folder'); closeCtx()"
                        class="ctx-item"><flux:icon.folder-plus class="size-4 text-zinc-500" /> New Folder</button>
                <div class="my-1 border-t border-white/8"></div>
            </div>
        </template>

        <button x-on:click="$wire.call('prepareRename', ctx.item.path); closeCtx()"
                class="ctx-item"><flux:icon.pencil class="size-4 text-zinc-500" /> Rename</button>
        <button x-on:click="navigator.clipboard?.writeText(ctx.item.path); closeCtx()"
                class="ctx-item"><flux:icon.clipboard class="size-4 text-zinc-500" /> Copy Path</button>
        <div class="my-1 border-t border-white/8"></div>
        <button x-on:click="$wire.call('prepareDelete', ctx.item.path, ctx.item.type === 'dir' ? 'dir' : 'file'); closeCtx()"
                class="ctx-item text-red-400 hover:bg-red-500/10">
            <flux:icon.trash class="size-4" /> Delete
        </button>
    </div>
</template>

{{-- ══════════════════════════════════════════════════════════════════════
     FLASH TOAST
══════════════════════════════════════════════════════════════════════ --}}
<div x-data="{ show: false, msg: '', type: 'success' }"
     x-on:show-flash.window="msg=$event.detail.message; type=$event.detail.type??'success'; show=true; setTimeout(()=>show=false,3000)"
     x-show="show"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 translate-y-2"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-end="opacity-0"
     class="fixed bottom-8 left-1/2 -translate-x-1/2 z-[300]
            flex items-center gap-2 px-4 py-2.5 rounded-xl shadow-2xl text-sm font-medium
            backdrop-blur border pointer-events-none"
     x-bind:class="type==='error'
        ? 'bg-red-950/90 border-red-500/30 text-red-200'
        : 'bg-zinc-800/90 border-white/12 text-zinc-100'"
     style="display:none">
    <flux:icon.check-circle x-show="type!=='error'" class="size-4 text-emerald-400 shrink-0" />
    <flux:icon.exclamation-circle x-show="type==='error'" class="size-4 text-red-400 shrink-0" style="display:none" />
    <span x-text="msg"></span>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     MODALS  (pure Alpine x-show — no Flux Pro required)
══════════════════════════════════════════════════════════════════════ --}}

{{-- Shared backdrop --}}
<div x-show="modal !== null"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-end="opacity-0"
     x-on:click="closeModal()"
     class="fixed inset-0 z-[400] bg-black/60 backdrop-blur-sm"
     style="display:none">
</div>

{{-- ── Modal: New File ─────────────────────────────────────────────── --}}
<div x-show="isModal('new-file')"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 scale-95"
     x-transition:enter-end="opacity-100 scale-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-end="opacity-0 scale-95"
     class="fixed inset-0 z-[500] flex items-center justify-center p-4 pointer-events-none"
     style="display:none">
    <div class="bg-zinc-900 border border-white/12 rounded-2xl shadow-2xl w-full max-w-sm pointer-events-auto"
         x-on:click.stop>
        <div class="flex items-center justify-between px-5 pt-5 pb-3">
            <h3 class="text-base font-semibold text-white">New File</h3>
            <button x-on:click="closeModal()" class="text-zinc-500 hover:text-zinc-200 transition">
                <flux:icon.x-mark class="size-5" />
            </button>
        </div>
        <p class="px-5 pb-3 text-sm text-zinc-400">
            In <code class="bg-white/8 px-1.5 py-0.5 rounded text-xs">{{ $currentDir ?: '/' }}</code>
        </p>
        <div class="px-5 pb-5 space-y-4">
            <div>
                <label class="block text-xs font-medium text-zinc-400 mb-1.5">File name</label>
                <input wire:model="newFileName" type="text"
                       placeholder="index.html"
                       wire:keydown.enter="createFileAndClose"
                       x-on:keydown.enter="$wire.createFileAndClose().then(() => closeModal())"
                       class="w-full bg-white/8 border border-white/15 text-zinc-100 text-sm
                              rounded-lg px-3 py-2 font-mono
                              focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                @error('newFileName') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex gap-2">
                <button wire:click="createFileAndClose"
                        x-on:click="$wire.createFileAndClose().then(() => closeModal())"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white text-sm
                               font-medium py-2 rounded-lg transition">
                    Create File
                </button>
                <button x-on:click="closeModal()"
                        class="px-4 bg-white/8 hover:bg-white/14 text-zinc-300 text-sm rounded-lg transition">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Modal: New Folder ────────────────────────────────────────────── --}}
<div x-show="isModal('new-folder')"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 scale-95"
     x-transition:enter-end="opacity-100 scale-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-end="opacity-0 scale-95"
     class="fixed inset-0 z-[500] flex items-center justify-center p-4 pointer-events-none"
     style="display:none">
    <div class="bg-zinc-900 border border-white/12 rounded-2xl shadow-2xl w-full max-w-sm pointer-events-auto"
         x-on:click.stop>
        <div class="flex items-center justify-between px-5 pt-5 pb-3">
            <h3 class="text-base font-semibold text-white">New Folder</h3>
            <button x-on:click="closeModal()" class="text-zinc-500 hover:text-zinc-200 transition">
                <flux:icon.x-mark class="size-5" />
            </button>
        </div>
        <div class="px-5 pb-5 space-y-4">
            <div>
                <label class="block text-xs font-medium text-zinc-400 mb-1.5">Folder name</label>
                <input wire:model="newFolderName" type="text"
                       placeholder="assets"
                       wire:keydown.enter="createFolderAndClose"
                       x-on:keydown.enter="$wire.createFolderAndClose().then(() => closeModal())"
                       class="w-full bg-white/8 border border-white/15 text-zinc-100 text-sm
                              rounded-lg px-3 py-2 font-mono
                              focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('newFolderName') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex gap-2">
                <button wire:click="createFolderAndClose"
                        x-on:click="$wire.createFolderAndClose().then(() => closeModal())"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white text-sm
                               font-medium py-2 rounded-lg transition">
                    Create Folder
                </button>
                <button x-on:click="closeModal()"
                        class="px-4 bg-white/8 hover:bg-white/14 text-zinc-300 text-sm rounded-lg transition">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Modal: Upload ────────────────────────────────────────────────── --}}
<div x-show="isModal('upload')"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 scale-95"
     x-transition:enter-end="opacity-100 scale-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-end="opacity-0 scale-95"
     class="fixed inset-0 z-[500] flex items-center justify-center p-4 pointer-events-none"
     style="display:none">
    <div class="bg-zinc-900 border border-white/12 rounded-2xl shadow-2xl w-full max-w-md pointer-events-auto"
         x-on:click.stop>
        <div class="flex items-center justify-between px-5 pt-5 pb-3">
            <h3 class="text-base font-semibold text-white">Upload Files</h3>
            <button x-on:click="closeModal()" class="text-zinc-500 hover:text-zinc-200 transition">
                <flux:icon.x-mark class="size-5" />
            </button>
        </div>
        <p class="px-5 pb-3 text-sm text-zinc-400">
            Upload to <code class="bg-white/8 px-1.5 py-0.5 rounded text-xs">{{ $currentDir ?: '/' }}</code>
            — max {{ config('filesystems.max_upload_mb', 50) }} MB
        </p>
        <div class="px-5 pb-5 space-y-4">
            <label class="flex flex-col items-center justify-center gap-3 w-full h-32
                          border-2 border-dashed border-white/15 rounded-xl cursor-pointer
                          hover:border-indigo-500/60 hover:bg-indigo-500/5 transition group">
                <flux:icon.arrow-up-tray class="size-8 text-zinc-600 group-hover:text-indigo-400 transition" />
                <span class="text-sm text-zinc-500 group-hover:text-zinc-300 transition text-center">
                    Drop files here or <span class="text-indigo-400">click to browse</span>
                </span>
                <input type="file" wire:model="upload"
                       x-on:change="$wire.uploadFile().then(() => closeModal())"
                       class="hidden" multiple>
            </label>

            <div wire:loading wire:target="upload"
                 class="flex items-center gap-2 text-sm text-zinc-400">
                <svg class="animate-spin size-4 text-indigo-400" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                Uploading…
            </div>

            @error('upload')
                <p class="text-red-400 text-sm bg-red-500/10 border border-red-500/20 rounded-lg px-3 py-2">
                    {{ $message }}
                </p>
            @enderror
        </div>
    </div>
</div>

{{-- ── Modal: Delete Confirm ────────────────────────────────────────── --}}
<div x-show="isModal('delete-confirm')"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 scale-95"
     x-transition:enter-end="opacity-100 scale-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-end="opacity-0 scale-95"
     class="fixed inset-0 z-[500] flex items-center justify-center p-4 pointer-events-none"
     style="display:none">
    <div class="bg-zinc-900 border border-white/12 rounded-2xl shadow-2xl w-full max-w-sm pointer-events-auto"
         x-on:click.stop>
        <div class="p-5">
            <div class="flex gap-3 mb-4">
                <div class="flex items-center justify-center size-10 rounded-full bg-red-500/15 shrink-0">
                    <flux:icon.trash class="size-5 text-red-400" />
                </div>
                <div>
                    <h3 class="text-base font-semibold text-white">
                        Delete {{ $deleteTargetType === 'dir' ? 'folder' : 'file' }}?
                    </h3>
                    <p class="text-sm text-zinc-400 mt-1">
                        <code class="text-zinc-200 bg-white/8 px-1.5 py-0.5 rounded text-xs font-mono">
                            {{ $deleteTargetName }}
                        </code>
                        will be permanently deleted.
                        @if ($deleteTargetType === 'dir')
                            <span class="text-red-400">All files inside will be lost.</span>
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex gap-2">
                <button wire:click="executeDelete"
                        x-on:click="$wire.executeDelete().then(() => closeModal())"
                        class="flex-1 bg-red-600 hover:bg-red-500 text-white text-sm
                               font-medium py-2 rounded-lg transition">
                    Delete
                </button>
                <button x-on:click="closeModal()"
                        class="flex-1 bg-white/8 hover:bg-white/14 text-zinc-300 text-sm rounded-lg transition">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Modal: Settings (slide-in from right) ────────────────────────── --}}
<div x-show="isModal('settings')"
     x-transition:enter="transition ease-out duration-250"
     x-transition:enter-start="opacity-0 translate-x-8"
     x-transition:enter-end="opacity-100 translate-x-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-end="opacity-0 translate-x-8"
     class="fixed inset-y-0 right-0 z-[500] flex pointer-events-none"
     style="display:none">
    <div class="w-80 bg-zinc-900 border-l border-white/12 shadow-2xl flex flex-col pointer-events-auto"
         x-on:click.stop>
        <div class="flex items-center justify-between px-5 py-4 border-b border-white/8">
            <h3 class="text-base font-semibold text-white">Page Settings</h3>
            <button x-on:click="closeModal()" class="text-zinc-500 hover:text-zinc-200 transition">
                <flux:icon.x-mark class="size-5" />
            </button>
        </div>

        <div class="flex-1 px-5 py-5 space-y-4 overflow-y-auto">
            <div>
                <label class="block text-xs font-medium text-zinc-400 mb-1.5">Page Name</label>
                <input wire:model="pageName" type="text"
                       class="w-full bg-white/8 border border-white/15 text-zinc-100 text-sm
                              rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('pageName') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-zinc-400 mb-1.5">Slug</label>
                <input wire:model="pageSlug" type="text"
                       class="w-full bg-white/8 border border-white/15 text-zinc-100 text-sm
                              rounded-lg px-3 py-2 font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('pageSlug') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                <p class="text-[11px] text-zinc-500 mt-1">
                    {{ config('app.base_domain') }}/<span class="text-indigo-300">{{ $pageSlug }}</span>
                </p>
            </div>

            <label class="flex items-center gap-3 cursor-pointer group">
                <div class="relative">
                    <input type="checkbox" wire:model="pageIsPublic" class="sr-only peer">
                    <div class="w-9 h-5 bg-white/10 peer-checked:bg-indigo-600 rounded-full transition peer-focus:ring-2 peer-focus:ring-indigo-500 peer-focus:ring-offset-1 peer-focus:ring-offset-zinc-900"></div>
                    <div class="absolute top-0.5 left-0.5 size-4 bg-white rounded-full transition peer-checked:translate-x-4 peer-checked:bg-white"></div>
                </div>
                <div>
                    <p class="text-sm text-zinc-300 font-medium">Publicly accessible</p>
                    <p class="text-xs text-zinc-500">Anyone can view this page's content</p>
                </div>
            </label>

            <div class="p-3 rounded-xl bg-white/4 border border-white/8 space-y-2.5">
                <p class="text-[10px] text-zinc-500 uppercase tracking-wider font-semibold">Access URLs</p>
                <div>
                    <p class="text-[10px] text-zinc-600 mb-0.5">Subdomain</p>
                    <a href="{{ $page->subdomainUrl() }}" target="_blank"
                       class="text-xs text-indigo-400 hover:underline font-mono break-all">
                        {{ $page->subdomainUrl() }}
                    </a>
                </div>
                <div>
                    <p class="text-[10px] text-zinc-600 mb-0.5">Path-based</p>
                    <a href="{{ $page->pathUrl() }}" target="_blank"
                       class="text-xs text-indigo-400 hover:underline font-mono break-all">
                        {{ $page->pathUrl() }}
                    </a>
                </div>
            </div>
        </div>

        <div class="px-5 py-4 border-t border-white/8 space-y-2">
            <button wire:click="saveSettings"
                    x-on:click="$wire.saveSettings().then(ok => ok !== false && closeModal())"
                    class="w-full bg-indigo-600 hover:bg-indigo-500 text-white text-sm
                           font-medium py-2 rounded-lg transition">
                <span wire:loading.remove wire:target="saveSettings">Save Settings</span>
                <span wire:loading wire:target="saveSettings">Saving…</span>
            </button>
            <button wire:click="deletePage"
                    x-on:click="confirm('Delete this page and ALL files? This cannot be undone.') && $wire.deletePage()"
                    class="w-full text-sm text-red-400 hover:text-red-300 hover:bg-red-500/10
                           py-2 rounded-lg transition flex items-center justify-center gap-1.5">
                <flux:icon.trash class="size-4" /> Delete this page
            </button>
        </div>
    </div>
</div>

</div>

<style>
.ctx-item {
    @apply w-full flex items-center gap-2.5 px-3 py-1.5 text-sm text-zinc-300
           hover:text-white hover:bg-white/8 transition;
}
/* Custom checkbox toggle */
input[type="checkbox"].sr-only:checked ~ div {
    background-color: rgb(99 102 241);
}
input[type="checkbox"].sr-only:checked ~ div + div {
    transform: translateX(1rem);
}
</style>
