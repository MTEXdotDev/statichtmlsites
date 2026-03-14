{{--
  File Manager — full-screen IDE layout.

  Alpine root: fileManager() from resources/js/file-manager.js
  Livewire:    App\Livewire\FileManager

  Conventions:
  - x-on: prefix everywhere (avoids @livewire Blade directive collision)
  - Flux modals for new-file, new-folder, rename, delete-confirm, settings
  - Context menu fully in Alpine
--}}

<div
    class="h-screen flex flex-col bg-zinc-950 text-zinc-100 overflow-hidden select-none"
    x-data="fileManager()"
    x-on:keydown.ctrl.s.window.prevent="save()"
    x-on:keydown.meta.s.window.prevent="save()"
>

    {{-- ═══════════════════════════════════════════════════════════════════════
         TOP BAR
    ═══════════════════════════════════════════════════════════════════════ --}}
    <header class="h-11 shrink-0 flex items-center gap-3 px-3 border-b border-white/8 bg-zinc-900/80 backdrop-blur">

        {{-- Back --}}
        <a href="{{ route('dashboard') }}"
           class="flex items-center gap-1 text-xs text-zinc-500 hover:text-zinc-100 transition px-1.5 py-1 rounded-md hover:bg-white/8">
            <flux:icon.arrow-left class="size-3.5" />
            <span class="hidden sm:inline">Dashboard</span>
        </a>

        <div class="w-px h-5 bg-white/10 shrink-0"></div>

        {{-- Page info --}}
        <div class="flex items-center gap-2 min-w-0">
            <span class="text-sm font-medium text-zinc-200 truncate max-w-40">{{ $page->name }}</span>
            <code class="text-[11px] bg-white/8 text-indigo-300 px-1.5 py-0.5 rounded font-mono shrink-0">
                {{ $page->slug }}
            </code>
            @if ($page->is_public)
                <span class="hidden sm:flex items-center gap-1 text-[11px] text-emerald-400">
                    <flux:icon.globe-alt class="size-3" /> Public
                </span>
            @else
                <span class="hidden sm:flex items-center gap-1 text-[11px] text-zinc-500">
                    <flux:icon.lock-closed class="size-3" /> Private
                </span>
            @endif
        </div>

        <div class="flex-1"></div>

        {{-- Unsaved indicator --}}
        @if ($isDirty)
            <span class="flex items-center gap-1 text-[11px] text-amber-400 animate-pulse shrink-0">
                <span class="size-1.5 rounded-full bg-amber-400"></span>
                Unsaved
            </span>
        @endif

        {{-- Preview --}}
        <a href="{{ route('pages.preview', $page->slug) }}"
           target="_blank"
           class="hidden sm:flex items-center gap-1.5 text-xs px-2.5 py-1.5 rounded-md
                  bg-white/8 hover:bg-white/14 text-zinc-300 hover:text-white transition">
            <flux:icon.eye class="size-3.5" />
            Preview
        </a>

        {{-- Open in new tab --}}
        <a href="{{ $page->subdomainUrl() }}" target="_blank" rel="noopener"
           class="hidden sm:flex items-center gap-1.5 text-xs px-2.5 py-1.5 rounded-md
                  bg-indigo-600 hover:bg-indigo-500 text-white transition">
            <flux:icon.arrow-top-right-on-square class="size-3.5" />
            Open Site
        </a>

        {{-- Settings --}}
        <button
            x-on:click="$modal.show('settings')"
            class="flex items-center gap-1 text-zinc-400 hover:text-zinc-100 px-1.5 py-1.5
                   rounded-md hover:bg-white/8 transition">
            <flux:icon.cog-6-tooth class="size-4" />
        </button>
    </header>


    {{-- ═══════════════════════════════════════════════════════════════════════
         MAIN SPLIT PANE
    ═══════════════════════════════════════════════════════════════════════ --}}
    <div class="flex flex-1 overflow-hidden">

        {{-- ── LEFT SIDEBAR ──────────────────────────────────────────────── --}}
        <aside class="w-56 lg:w-60 shrink-0 flex flex-col border-r border-white/8 bg-zinc-900/40">

            {{-- Sidebar toolbar --}}
            <div class="h-9 flex items-center gap-1 px-2 border-b border-white/8 shrink-0">
                <span class="text-[11px] text-zinc-600 uppercase tracking-wider font-medium px-1 flex-1">
                    Explorer
                </span>
                <button
                    x-on:click="$modal.show('new-file')"
                    class="p-1 rounded text-zinc-500 hover:text-zinc-100 hover:bg-white/8 transition"
                    title="New File">
                    <flux:icon.document-plus class="size-4" />
                </button>
                <button
                    x-on:click="$modal.show('new-folder')"
                    class="p-1 rounded text-zinc-500 hover:text-zinc-100 hover:bg-white/8 transition"
                    title="New Folder">
                    <flux:icon.folder-plus class="size-4" />
                </button>
                <button
                    x-on:click="$modal.show('upload')"
                    class="p-1 rounded text-zinc-500 hover:text-zinc-100 hover:bg-white/8 transition"
                    title="Upload Files">
                    <flux:icon.arrow-up-tray class="size-4" />
                </button>
                <button
                    wire:click="$refresh"
                    class="p-1 rounded text-zinc-500 hover:text-zinc-100 hover:bg-white/8 transition"
                    title="Refresh">
                    <flux:icon.arrow-path class="size-3.5" />
                </button>
            </div>

            {{-- Current directory breadcrumb --}}
            @if ($currentDir)
            <div class="flex items-center gap-1 px-3 py-1.5 border-b border-white/8 bg-white/3">
                <button wire:click="goUp"
                        class="flex items-center gap-1 text-[11px] text-zinc-500 hover:text-zinc-200 transition">
                    <flux:icon.arrow-uturn-left class="size-3" />
                </button>
                <span class="text-[11px] text-zinc-500 truncate font-mono">
                    / {{ $currentDir }}
                </span>
            </div>
            @endif

            {{-- File tree --}}
            <div class="flex-1 overflow-y-auto py-1 min-h-0 scrollbar-thin scrollbar-thumb-white/10">
                @forelse ($this->fileTree as $item)
                    @include('livewire.partials.tree-item', ['item' => $item, 'depth' => 0])
                @empty
                    <div class="flex flex-col items-center justify-center h-32 text-zinc-600">
                        <flux:icon.document class="size-8 mb-2 opacity-30" />
                        <p class="text-xs">No files yet</p>
                        <button
                            x-on:click="$modal.show('new-file')"
                            class="text-xs text-indigo-400 hover:text-indigo-300 mt-2 transition">
                            Create index.html
                        </button>
                    </div>
                @endforelse
            </div>

            {{-- Rename inline form (shown when renameTarget is set) --}}
            @if ($renameTarget !== null)
            <div class="border-t border-white/8 p-2 bg-zinc-900/60">
                <p class="text-[11px] text-zinc-500 mb-1.5 flex items-center gap-1">
                    <flux:icon.pencil class="size-3" /> Rename
                </p>
                <input
                    wire:model="renameTo"
                    type="text"
                    autofocus
                    wire:keydown.enter="confirmRename"
                    wire:keydown.escape="$set('renameTarget', null)"
                    class="w-full bg-white/8 border border-white/15 text-zinc-100 text-xs rounded-md
                           px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-indigo-500 font-mono">
                <div class="flex gap-1.5 mt-1.5">
                    <button wire:click="confirmRename"
                            class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white text-xs
                                   rounded-md py-1 font-medium transition">
                        Rename
                    </button>
                    <button wire:click="$set('renameTarget', null)"
                            class="flex-1 bg-white/8 hover:bg-white/14 text-zinc-300 text-xs
                                   rounded-md py-1 transition">
                        Cancel
                    </button>
                </div>
            </div>
            @endif

        </aside>


        {{-- ── CENTER EDITOR PANE ──────────────────────────────────────── --}}
        <div class="flex-1 flex flex-col overflow-hidden min-w-0">

            {{-- Editor toolbar --}}
            <div class="h-9 flex items-center gap-2 px-3 border-b border-white/8 bg-zinc-900/30 shrink-0">
                @if ($activeFile)
                    <flux:icon.document class="size-3.5 text-zinc-600 shrink-0" />
                    <span class="text-xs text-zinc-400 font-mono truncate flex-1">
                        {{ $activeFile }}
                    </span>
                @else
                    <span class="text-xs text-zinc-600 flex-1 italic">No file open</span>
                @endif

                <div class="flex items-center gap-2 shrink-0">
                    @if ($isDirty)
                        <button
                            x-on:click="save()"
                            class="flex items-center gap-1 text-xs px-2 py-1 rounded-md
                                   bg-indigo-600 hover:bg-indigo-500 text-white transition">
                            <flux:icon.check class="size-3" />
                            Save
                            <kbd class="text-[10px] opacity-50 ml-0.5">⌘S</kbd>
                        </button>
                    @elseif ($activeFile && $this->isEditable)
                        <span class="flex items-center gap-1 text-[11px] text-emerald-500">
                            <flux:icon.check-circle class="size-3" />
                            Saved
                        </span>
                    @endif

                    @if ($activeFile && $this->isPreviewable && !$this->isEditable)
                        <a href="{{ $this->previewUrl }}" target="_blank" rel="noopener"
                           class="flex items-center gap-1 text-xs px-2 py-1 rounded-md
                                  bg-white/8 hover:bg-white/14 text-zinc-300 transition">
                            <flux:icon.arrow-top-right-on-square class="size-3" />
                            Open
                        </a>
                    @endif
                </div>
            </div>

            {{-- ── Content area ── --}}

            @if ($this->isEditable && $activeFile)
                {{-- CodeMirror host --}}
                <div id="cm-host" class="flex-1 overflow-hidden min-h-0"></div>

            @elseif ($this->isPreviewable && $activeFile)
                {{-- Media / binary preview --}}
                @php $ext = strtolower(pathinfo($activeFile, PATHINFO_EXTENSION)); @endphp
                <div class="flex-1 flex items-center justify-center bg-zinc-950/60 p-8 min-h-0">
                    @if (in_array($ext, ['png','jpg','jpeg','gif','webp','ico']))
                        <img src="{{ $this->previewUrl }}"
                             class="max-h-full max-w-full object-contain rounded-lg shadow-2xl ring-1 ring-white/10">
                    @elseif ($ext === 'svg')
                        <img src="{{ $this->previewUrl }}"
                             class="max-h-96 max-w-full object-contain rounded-lg bg-white/5 p-4">
                    @elseif (in_array($ext, ['mp4','webm']))
                        <video src="{{ $this->previewUrl }}" controls
                               class="max-h-full max-w-full rounded-lg shadow-2xl"></video>
                    @elseif (in_array($ext, ['mp3','wav','ogg']))
                        <div class="text-center">
                            <flux:icon.musical-note class="size-16 text-zinc-600 mx-auto mb-4" />
                            <p class="text-sm text-zinc-400 mb-4">{{ basename($activeFile) }}</p>
                            <audio src="{{ $this->previewUrl }}" controls class="w-80"></audio>
                        </div>
                    @elseif ($ext === 'pdf')
                        <iframe src="{{ $this->previewUrl }}"
                                class="w-full h-full rounded-lg border-0"></iframe>
                    @endif
                </div>

            @elseif ($activeFile)
                {{-- Non-previewable binary --}}
                <div class="flex-1 flex flex-col items-center justify-center text-zinc-600">
                    <flux:icon.document class="size-16 mb-3 opacity-30" />
                    <p class="text-sm">{{ basename($activeFile) }}</p>
                    <p class="text-xs mt-1 opacity-60">Binary file — cannot be edited in browser</p>
                </div>

            @else
                {{-- Empty state --}}
                <div class="flex-1 flex flex-col items-center justify-center text-zinc-700">
                    <flux:icon.code-bracket-square class="size-20 mb-4 opacity-20" />
                    <p class="text-base font-medium mb-1">No file open</p>
                    <p class="text-sm opacity-60">Select a file from the explorer to start editing</p>
                    <button
                        x-on:click="$modal.show('new-file')"
                        class="mt-4 flex items-center gap-1.5 text-sm text-indigo-400 hover:text-indigo-300 transition">
                        <flux:icon.plus class="size-4" />
                        Create a new file
                    </button>
                </div>
            @endif

        </div>

    </div>


    {{-- ═══════════════════════════════════════════════════════════════════════
         STATUS BAR
    ═══════════════════════════════════════════════════════════════════════ --}}
    <footer class="h-6 shrink-0 flex items-center justify-between px-3 bg-indigo-900/40
                   border-t border-white/8 text-[11px] text-zinc-500">
        <div class="flex items-center gap-4">
            @if ($activeFile)
                <span class="font-mono">{{ basename($activeFile) }}</span>
                @if ($this->isEditable)
                    <span
                        x-data="{ line: 1, col: 1 }"
                        x-on:cm-cursor.window="line = $event.detail.line; col = $event.detail.col"
                        class="tabular-nums">
                        Ln <span x-text="line">1</span>,
                        Col <span x-text="col">1</span>
                    </span>
                    <span class="capitalize">{{ $this->editorLanguage }}</span>
                @endif
            @else
                <span>{{ config('app.name') }}</span>
            @endif
        </div>
        <div class="flex items-center gap-3">
            <span>{{ $page->slug }}</span>
            <span class="{{ $page->is_public ? 'text-emerald-500' : 'text-zinc-600' }}">
                {{ $page->is_public ? '🌐 Public' : '🔒 Private' }}
            </span>
        </div>
    </footer>


    {{-- ═══════════════════════════════════════════════════════════════════════
         CONTEXT MENU (Alpine, fully client-side)
    ═══════════════════════════════════════════════════════════════════════ --}}
    <template x-if="ctx.show">
        <div
            class="fixed z-[100] min-w-40 bg-zinc-800 border border-white/12 rounded-lg shadow-2xl
                   py-1 text-sm overflow-hidden"
            x-bind:style="`top: ${Math.min(ctx.y, window.innerHeight - 180)}px; left: ${Math.min(ctx.x, window.innerWidth - 180)}px`"
            x-on:click.stop
        >
            <template x-if="ctx.item?.type === 'dir'">
                <div>
                    <button
                        x-on:click="$modal.show('new-file'); closeContext()"
                        class="w-full flex items-center gap-2.5 px-3 py-1.5 hover:bg-white/8 text-zinc-300 hover:text-white transition">
                        <flux:icon.document-plus class="size-4 text-zinc-500" />
                        New File Here
                    </button>
                    <button
                        x-on:click="$modal.show('new-folder'); closeContext()"
                        class="w-full flex items-center gap-2.5 px-3 py-1.5 hover:bg-white/8 text-zinc-300 hover:text-white transition">
                        <flux:icon.folder-plus class="size-4 text-zinc-500" />
                        New Folder Here
                    </button>
                    <div class="my-1 border-t border-white/8"></div>
                </div>
            </template>

            <button
                x-on:click="$wire.call('prepareRename', ctx.item.path); closeContext()"
                class="w-full flex items-center gap-2.5 px-3 py-1.5 hover:bg-white/8 text-zinc-300 hover:text-white transition">
                <flux:icon.pencil class="size-4 text-zinc-500" />
                Rename
            </button>

            <button
                x-on:click="navigator.clipboard.writeText(ctx.item.path); closeContext()"
                class="w-full flex items-center gap-2.5 px-3 py-1.5 hover:bg-white/8 text-zinc-300 hover:text-white transition">
                <flux:icon.clipboard class="size-4 text-zinc-500" />
                Copy Path
            </button>

            <div class="my-1 border-t border-white/8"></div>

            <button
                x-on:click="$wire.call('prepareDelete', ctx.item.path, ctx.item.type === 'dir' ? 'dir' : 'file'); closeContext()"
                class="w-full flex items-center gap-2.5 px-3 py-1.5 hover:bg-red-500/15 text-zinc-400 hover:text-red-400 transition">
                <flux:icon.trash class="size-4" />
                Delete
            </button>
        </div>
    </template>


    {{-- ═══════════════════════════════════════════════════════════════════════
         FLASH TOAST
    ═══════════════════════════════════════════════════════════════════════ --}}
    <div
        x-data="{ show: false, msg: '', type: 'success' }"
        x-on:show-flash.window="
            msg  = $event.detail.message;
            type = $event.detail.type ?? 'success';
            show = true;
            setTimeout(() => show = false, 3000)
        "
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed bottom-8 left-1/2 -translate-x-1/2 z-[200]
               flex items-center gap-2 px-4 py-2.5 rounded-xl shadow-2xl text-sm font-medium
               backdrop-blur border"
        x-bind:class="type === 'error'
            ? 'bg-red-950/90 border-red-500/30 text-red-200'
            : 'bg-zinc-800/90 border-white/12 text-zinc-100'"
        style="display:none"
    >
        <template x-if="type !== 'error'">
            <flux:icon.check-circle class="size-4 text-emerald-400 shrink-0" />
        </template>
        <template x-if="type === 'error'">
            <flux:icon.exclamation-circle class="size-4 text-red-400 shrink-0" />
        </template>
        <span x-text="msg"></span>
    </div>


    {{-- ═══════════════════════════════════════════════════════════════════════
         MODALS
    ═══════════════════════════════════════════════════════════════════════ --}}

    {{-- ── New File ────────────────────────────────────────────────────── --}}
    <flux:modal name="new-file" class="max-w-sm">
        <div class="p-5">
            <flux:heading size="lg" class="mb-1">New File</flux:heading>
            <flux:subheading class="mb-5">
                Create a new file in
                <code class="text-xs bg-white/8 px-1 py-0.5 rounded">
                    {{ $currentDir ?: '/' }}
                </code>
            </flux:subheading>

            <flux:field>
                <flux:label>File name</flux:label>
                <flux:input
                    wire:model="newFileName"
                    placeholder="index.html"
                    autofocus
                    wire:keydown.enter="createFileAndClose" />
                @error('newFileName') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <div class="flex gap-2 mt-5">
                <flux:button
                    wire:click="createFileAndClose"
                    variant="primary"
                    class="flex-1">
                    Create File
                </flux:button>
                <flux:button
                    x-on:click="$modal.close('new-file')"
                    variant="ghost">
                    Cancel
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- ── New Folder ──────────────────────────────────────────────────── --}}
    <flux:modal name="new-folder" class="max-w-sm">
        <div class="p-5">
            <flux:heading size="lg" class="mb-1">New Folder</flux:heading>
            <flux:subheading class="mb-5">Create a folder inside
                <code class="text-xs bg-white/8 px-1 py-0.5 rounded">
                    {{ $currentDir ?: '/' }}
                </code>
            </flux:subheading>

            <flux:field>
                <flux:label>Folder name</flux:label>
                <flux:input
                    wire:model="newFolderName"
                    placeholder="assets"
                    wire:keydown.enter="createFolderAndClose" />
                @error('newFolderName') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <div class="flex gap-2 mt-5">
                <flux:button
                    wire:click="createFolderAndClose"
                    variant="primary"
                    class="flex-1">
                    Create Folder
                </flux:button>
                <flux:button
                    x-on:click="$modal.close('new-folder')"
                    variant="ghost">
                    Cancel
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- ── Upload ──────────────────────────────────────────────────────── --}}
    <flux:modal name="upload" class="max-w-md">
        <div class="p-5">
            <flux:heading size="lg" class="mb-1">Upload Files</flux:heading>
            <flux:subheading class="mb-5">
                Upload to <code class="text-xs bg-white/8 px-1 py-0.5 rounded">{{ $currentDir ?: '/' }}</code>
                — max {{ config('filesystems.max_upload_mb', 50) }} MB per file
            </flux:subheading>

            <label
                class="flex flex-col items-center justify-center gap-3 w-full h-36
                       border-2 border-dashed border-white/15 rounded-xl cursor-pointer
                       hover:border-indigo-500/50 hover:bg-indigo-500/5 transition group"
                x-on:dragover.prevent
                x-on:drop.prevent="
                    const files = Array.from($event.dataTransfer.files);
                    $wire.upload('upload', files[0])
                ">
                <flux:icon.arrow-up-tray class="size-8 text-zinc-600 group-hover:text-indigo-400 transition" />
                <span class="text-sm text-zinc-500 group-hover:text-zinc-300 transition">
                    Drop files here, or <span class="text-indigo-400">click to browse</span>
                </span>
                <input
                    type="file"
                    wire:model="upload"
                    x-on:change="$wire.uploadFile()"
                    class="hidden"
                    multiple>
            </label>

            <div wire:loading wire:target="upload" class="flex items-center gap-2 mt-3 text-sm text-zinc-400">
                <svg class="animate-spin size-4 text-indigo-400" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                Uploading…
            </div>

            @error('upload')
                <flux:callout variant="danger" icon="exclamation-circle" class="mt-3">
                    {{ $message }}
                </flux:callout>
            @enderror

            <div class="mt-4 flex justify-end">
                <flux:button
                    x-on:click="$modal.close('upload')"
                    variant="ghost">
                    Done
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- ── Delete Confirmation ─────────────────────────────────────────── --}}
    <flux:modal name="delete-confirm" class="max-w-sm">
        <div class="p-5">
            <div class="flex items-start gap-3 mb-4">
                <div class="flex items-center justify-center size-10 rounded-full bg-red-500/15 shrink-0">
                    <flux:icon.trash class="size-5 text-red-400" />
                </div>
                <div>
                    <flux:heading size="lg">
                        Delete {{ $deleteTargetType === 'dir' ? 'folder' : 'file' }}?
                    </flux:heading>
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
                <flux:button
                    wire:click="executeDelete"
                    variant="danger"
                    class="flex-1">
                    Delete
                </flux:button>
                <flux:button
                    x-on:click="$modal.close('delete-confirm')"
                    variant="ghost"
                    class="flex-1">
                    Cancel
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- ── Page Settings ───────────────────────────────────────────────── --}}
    <flux:modal name="settings" class="max-w-md" variant="flyout">
        <div class="p-5">
            <flux:heading size="lg" class="mb-1">Page Settings</flux:heading>
            <flux:subheading class="mb-6">
                Configure your page's name, URL slug, and visibility.
            </flux:subheading>

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Page Name</flux:label>
                    <flux:input wire:model="pageName" type="text" />
                    @error('pageName') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>

                <flux:field>
                    <flux:label>Slug</flux:label>
                    <flux:input wire:model="pageSlug" type="text" class="font-mono" />
                    @error('pageSlug') <flux:error>{{ $message }}</flux:error> @enderror
                    <flux:description>
                        {{ config('app.base_domain') }}/<strong>{{ $pageSlug }}</strong>
                    </flux:description>
                </flux:field>

                <flux:field variant="inline">
                    <flux:switch wire:model="pageIsPublic" />
                    <flux:label>Publicly accessible</flux:label>
                    <flux:description>When off, only you can view this page.</flux:description>
                </flux:field>
            </div>

            {{-- URLs --}}
            <div class="mt-5 p-3 rounded-lg bg-white/4 border border-white/8 space-y-2">
                <p class="text-[11px] text-zinc-500 uppercase tracking-wider font-medium">Access URLs</p>
                <div>
                    <p class="text-[11px] text-zinc-600 mb-0.5">Subdomain</p>
                    <a href="{{ $page->subdomainUrl() }}" target="_blank"
                       class="text-xs text-indigo-400 hover:underline font-mono break-all">
                        {{ $page->subdomainUrl() }}
                    </a>
                </div>
                <div>
                    <p class="text-[11px] text-zinc-600 mb-0.5">Path-based</p>
                    <a href="{{ $page->pathUrl() }}" target="_blank"
                       class="text-xs text-indigo-400 hover:underline font-mono break-all">
                        {{ $page->pathUrl() }}
                    </a>
                </div>
            </div>

            <div class="flex gap-2 mt-5">
                <flux:button
                    wire:click="saveSettings"
                    wire:loading.attr="disabled"
                    variant="primary"
                    class="flex-1">
                    <span wire:loading.remove wire:target="saveSettings">Save Settings</span>
                    <span wire:loading wire:target="saveSettings">Saving…</span>
                </flux:button>
                <flux:button
                    x-on:click="$modal.close('settings')"
                    variant="ghost">
                    Cancel
                </flux:button>
            </div>

            <div class="mt-4 pt-4 border-t border-white/8">
                <flux:button
                    wire:click="deletePage"
                    wire:confirm="Permanently delete '{{ $page->name }}' and all its files?\nThis cannot be undone."
                    variant="ghost"
                    class="w-full text-red-400 hover:text-red-300 hover:bg-red-500/10">
                    <flux:icon.trash class="size-4" />
                    Delete this page
                </flux:button>
            </div>
        </div>
    </flux:modal>

</div>
