{{-- File Manager — Full-screen IDE layout --}}
{{--
    NOTE: Alpine event listeners use the x-on: prefix throughout this template.
    Do NOT use the @ shorthand for Alpine here — Livewire registers @livewire
    as a Blade directive, and other @ names can conflict in edge cases.
    Livewire flash events use $this->dispatch('show-flash', message: '...')
    and are caught by x-on:show-flash.window below.
--}}

<div
    class="flex h-screen overflow-hidden bg-[#1e1e2e] text-gray-100 text-sm"
    x-data="fileManager()"
    x-init="initEditor()"
    x-on:keydown.ctrl.s.window.prevent="save()"
    x-on:load-editor.window="loadEditor($event.detail.content, $event.detail.language)"
>

    {{-- ── Flash toast ─────────────────────────────────────────────────────── --}}
    <div
        x-data="{ show: false, msg: '' }"
        x-on:show-flash.window="msg = $event.detail.message; show = true; setTimeout(() => show = false, 2500)"
        x-show="show"
        x-transition.opacity
        class="fixed top-4 right-4 z-50 bg-green-600 text-white text-xs
               px-3 py-2 rounded-lg shadow-lg pointer-events-none"
        x-text="msg"
        style="display:none"
    ></div>

    {{-- ── Left sidebar: file tree ─────────────────────────────────────────── --}}
    <aside class="w-56 flex-shrink-0 bg-[#181825] border-r border-[#313244] flex flex-col overflow-hidden">

        {{-- Page title + settings toggle --}}
        <div class="px-3 py-2 border-b border-[#313244] flex items-center justify-between gap-2">
            <span class="font-semibold text-xs text-[#cba6f7] truncate flex-1">{{ $page->name }}</span>
            <button wire:click="$set('showSettings', true)"
                    class="text-[#6c7086] hover:text-white transition flex-shrink-0" title="Settings">
                ⚙
            </button>
        </div>

        {{-- Current-directory breadcrumb --}}
        @if ($currentDir)
        <div class="px-3 py-1 flex items-center gap-1 text-xs text-[#6c7086] border-b border-[#313244]">
            <button wire:click="goUp" class="hover:text-white transition">↑ ..</button>
            <span class="truncate">/ {{ $currentDir }}</span>
        </div>
        @endif

        {{-- File list --}}
        <div class="flex-1 overflow-y-auto py-1 min-h-0">
            @forelse ($this->fileTree as $entry)
                @if ($entry['type'] === 'dir')
                    <button wire:click="enterDirectory('{{ $entry['path'] }}')"
                            class="w-full text-left px-3 py-1 hover:bg-[#313244] text-[#89b4fa]
                                   flex items-center gap-1.5 group text-xs">
                        <span>📁</span>
                        <span class="truncate flex-1">{{ $entry['name'] }}</span>
                        <span class="hidden group-hover:flex items-center gap-0.5">
                            <button wire:click.stop="startRename('{{ $entry['path'] }}')"
                                    class="text-[#6c7086] hover:text-white px-0.5">✎</button>
                            <button wire:click.stop="deleteFile('{{ $entry['path'] }}')"
                                    wire:confirm="Delete folder '{{ $entry['name'] }}' and all its contents?"
                                    class="text-[#6c7086] hover:text-red-400 px-0.5">✕</button>
                        </span>
                    </button>
                @else
                    <div class="flex items-center group text-xs
                                hover:bg-[#313244] {{ $entry['active'] ? 'bg-[#313244]' : '' }}">
                        <button wire:click="openFile('{{ $entry['path'] }}')"
                                class="flex-1 text-left px-3 py-1 flex items-center gap-1.5 min-w-0">
                            <span>{{ $entry['isIndex'] ? '🏠' : '📄' }}</span>
                            <span class="truncate {{ $entry['isIndex'] ? 'text-[#a6e3a1] font-medium' : 'text-gray-300' }}">
                                {{ $entry['name'] }}
                            </span>
                        </button>
                        <div class="hidden group-hover:flex items-center pr-2 gap-0.5 flex-shrink-0">
                            <button wire:click="startRename('{{ $entry['path'] }}')"
                                    class="text-[#6c7086] hover:text-white p-0.5">✎</button>
                            <button wire:click="deleteFile('{{ $entry['path'] }}')"
                                    wire:confirm="Delete '{{ $entry['name'] }}'?"
                                    class="text-[#6c7086] hover:text-red-400 p-0.5">✕</button>
                        </div>
                    </div>
                @endif
            @empty
                <p class="text-[#6c7086] text-xs px-3 py-2 italic">No files yet.</p>
            @endforelse
        </div>

        {{-- Inline rename --}}
        @if ($renameTarget !== null)
        <div class="px-3 py-2 border-t border-[#313244] bg-[#1e1e2e]">
            <p class="text-xs text-[#6c7086] mb-1">Rename</p>
            <input wire:model="renameTo" type="text" autofocus
                   wire:keydown.enter="confirmRename"
                   wire:keydown.escape="$set('renameTarget', null)"
                   class="w-full bg-[#313244] text-white text-xs rounded px-2 py-1
                          focus:outline-none focus:ring-1 focus:ring-[#cba6f7]">
            <div class="flex gap-1 mt-1.5">
                <button wire:click="confirmRename"
                        class="flex-1 bg-[#cba6f7] text-[#1e1e2e] text-xs rounded py-1 font-semibold">OK</button>
                <button wire:click="$set('renameTarget', null)"
                        class="flex-1 bg-[#313244] text-xs rounded py-1">Cancel</button>
            </div>
        </div>
        @endif

        {{-- New file / folder inputs --}}
        <div class="border-t border-[#313244] px-3 py-2 space-y-1.5">
            <div class="flex gap-1">
                <input wire:model="newFileName"
                       type="text"
                       placeholder="new-file.html"
                       wire:keydown.enter="createFile"
                       class="flex-1 min-w-0 bg-[#313244] text-white text-xs rounded px-2 py-1
                              focus:outline-none focus:ring-1 focus:ring-[#cba6f7]">
                <button wire:click="createFile"
                        class="text-xs bg-[#313244] hover:bg-[#45475a] px-2 rounded transition"
                        title="New file">+F</button>
            </div>
            <div class="flex gap-1">
                <input wire:model="newFolderName"
                       type="text"
                       placeholder="new-folder"
                       wire:keydown.enter="createFolder"
                       class="flex-1 min-w-0 bg-[#313244] text-white text-xs rounded px-2 py-1
                              focus:outline-none focus:ring-1 focus:ring-[#cba6f7]">
                <button wire:click="createFolder"
                        class="text-xs bg-[#313244] hover:bg-[#45475a] px-2 rounded transition"
                        title="New folder">+D</button>
            </div>
        </div>

        {{-- Upload --}}
        <div class="border-t border-[#313244] px-3 py-2">
            <label class="block cursor-pointer text-center text-xs py-1.5
                          bg-[#313244] hover:bg-[#45475a] rounded transition">
                ⬆ Upload file
                <input type="file"
                       wire:model="upload"
                       x-on:change="$wire.uploadFile()"
                       class="hidden">
            </label>
            <div wire:loading wire:target="upload"
                 class="text-center text-xs text-[#6c7086] mt-1">
                Uploading…
            </div>
            @error('upload')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

    </aside>

    {{-- ── Center: editor / preview pane ──────────────────────────────────── --}}
    <div class="flex-1 flex flex-col overflow-hidden min-w-0">

        {{-- Toolbar --}}
        <div class="flex items-center gap-3 px-4 py-2 bg-[#181825] border-b border-[#313244] flex-shrink-0">
            <span class="text-[#6c7086] text-xs truncate">
                {{ $activeFile ?: 'No file open' }}
            </span>

            <div class="ml-auto flex items-center gap-2">
                @if ($isDirty)
                    <span class="text-xs text-yellow-400">● Unsaved</span>
                @endif

                @if ($this->isEditable && $activeFile)
                    <button x-on:click="save()"
                            class="text-xs bg-[#313244] hover:bg-[#45475a] px-3 py-1 rounded transition">
                        Save <kbd class="opacity-40 text-[10px]">Ctrl+S</kbd>
                    </button>
                @endif

                <a href="{{ $page->subdomainUrl() }}" target="_blank" rel="noopener"
                   class="text-xs bg-[#cba6f7] hover:bg-[#d6acff] text-[#1e1e2e] font-medium px-3 py-1 rounded transition">
                    Preview ↗
                </a>
            </div>
        </div>

        {{-- CodeMirror host --}}
        @if ($this->isEditable && $activeFile)
            <div id="codemirror-host" class="flex-1 overflow-hidden min-h-0"></div>

        {{-- Binary / media preview --}}
        @elseif ($this->isPreviewable && $activeFile)
            @php $ext = strtolower(pathinfo($activeFile, PATHINFO_EXTENSION)); @endphp
            <div class="flex-1 flex items-center justify-center bg-[#1e1e2e] p-8 min-h-0">
                @if (in_array($ext, ['png','jpg','jpeg','gif','webp','svg','ico']))
                    <img src="{{ $this->previewUrl }}"
                         class="max-h-full max-w-full object-contain rounded shadow-lg">
                @elseif (in_array($ext, ['mp4','webm']))
                    <video src="{{ $this->previewUrl }}" controls
                           class="max-h-full max-w-full rounded shadow-lg"></video>
                @elseif (in_array($ext, ['mp3','wav','ogg']))
                    <audio src="{{ $this->previewUrl }}" controls class="rounded shadow-lg"></audio>
                @elseif ($ext === 'pdf')
                    <iframe src="{{ $this->previewUrl }}" class="w-full h-full rounded border-0"></iframe>
                @else
                    <div class="text-center text-[#6c7086]">
                        <p class="text-4xl mb-3">📎</p>
                        <p class="text-sm">{{ basename($activeFile) }}</p>
                        <p class="text-xs mt-1">Binary file — cannot be previewed</p>
                    </div>
                @endif
            </div>

        {{-- Non-previewable binary --}}
        @elseif ($activeFile)
            <div class="flex-1 flex items-center justify-center text-[#6c7086] text-sm">
                Binary file — not editable.
            </div>

        {{-- Nothing open --}}
        @else
            <div class="flex-1 flex flex-col items-center justify-center text-[#6c7086]">
                <p class="text-4xl mb-3">👈</p>
                <p>Select a file from the sidebar to edit.</p>
            </div>
        @endif

    </div>

    {{-- ── Settings slide-over ─────────────────────────────────────────────── --}}
    @if ($showSettings)
    <div class="fixed inset-0 z-40 flex justify-end bg-black/40"
         x-on:click.self="$wire.set('showSettings', false)">
        <div class="w-80 bg-[#181825] border-l border-[#313244] h-full flex flex-col shadow-2xl">

            <div class="px-5 py-4 border-b border-[#313244] flex items-center justify-between">
                <h2 class="font-semibold text-sm text-white">Page Settings</h2>
                <button wire:click="$set('showSettings', false)"
                        class="text-[#6c7086] hover:text-white transition">✕</button>
            </div>

            <div class="flex-1 px-5 py-5 space-y-4 overflow-y-auto">

                <div>
                    <label class="block text-xs text-[#6c7086] mb-1">Page name</label>
                    <input wire:model="pageName" type="text"
                           class="w-full bg-[#313244] text-white text-sm rounded px-3 py-2
                                  focus:outline-none focus:ring-1 focus:ring-[#cba6f7]">
                    @error('pageName')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs text-[#6c7086] mb-1">Slug</label>
                    <input wire:model="pageSlug" type="text"
                           class="w-full bg-[#313244] text-white text-sm rounded px-3 py-2 font-mono
                                  focus:outline-none focus:ring-1 focus:ring-[#cba6f7]">
                    @error('pageSlug')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-[10px] text-[#6c7086] mt-1">
                        {{ config('app.base_domain') }}/<strong class="text-[#cba6f7]">{{ $pageSlug }}</strong>
                    </p>
                </div>

                <div class="flex items-center gap-2.5">
                    <input type="checkbox" id="isPublic" wire:model="pageIsPublic"
                           class="h-4 w-4 rounded border-gray-600 text-[#cba6f7] focus:ring-[#cba6f7]">
                    <label for="isPublic" class="text-sm text-gray-300">Publicly accessible</label>
                </div>

                <div class="pt-1">
                    <p class="text-xs text-[#6c7086] mb-1.5">Access URLs</p>
                    <a href="{{ $page->subdomainUrl() }}" target="_blank"
                       class="block text-[10px] text-indigo-400 hover:underline truncate mb-1">
                        {{ $page->subdomainUrl() }}
                    </a>
                    <a href="{{ $page->pathUrl() }}" target="_blank"
                       class="block text-[10px] text-indigo-400 hover:underline truncate">
                        {{ $page->pathUrl() }}
                    </a>
                </div>

            </div>

            <div class="px-5 py-4 border-t border-[#313244] space-y-3">
                <button wire:click="saveSettings" wire:loading.attr="disabled"
                        class="w-full bg-[#cba6f7] hover:bg-[#d6acff] text-[#1e1e2e]
                               font-semibold text-sm py-2 rounded-lg transition
                               disabled:opacity-50">
                    <span wire:loading.remove wire:target="saveSettings">Save Settings</span>
                    <span wire:loading wire:target="saveSettings">Saving…</span>
                </button>

                <button wire:click="saveSettings"
                        wire:confirm="Delete this page and all its files? This cannot be undone."
                        wire:click="deletePage"
                        class="w-full text-xs text-red-400 hover:text-red-300 py-1.5 transition">
                    Delete this page and all files
                </button>
            </div>
        </div>
    </div>
    @endif

</div>

{{-- ── CodeMirror 6 (loaded via ESM import map in manager.blade.php head) ── --}}
<script type="module">
import { EditorState, Compartment }      from '@codemirror/state';
import { EditorView, keymap, lineNumbers,
         highlightActiveLineGutter,
         highlightActiveLine, drawSelection } from '@codemirror/view';
import { defaultKeymap, history,
         historyKeymap, indentWithTab }  from '@codemirror/commands';
import { indentOnInput, bracketMatching,
         foldGutter, syntaxHighlighting,
         defaultHighlightStyle }         from '@codemirror/language';
import { html }                          from '@codemirror/lang-html';
import { css }                           from '@codemirror/lang-css';
import { javascript }                    from '@codemirror/lang-javascript';
import { json }                          from '@codemirror/lang-json';
import { xml }                           from '@codemirror/lang-xml';
import { oneDark }                       from '@codemirror/theme-one-dark';

const langCompartment = new Compartment();
let   view            = null;
let   saveDebounce    = null;

function langExtension(lang) {
    switch (lang) {
        case 'html':       return html({ matchClosingTags: true });
        case 'css':        return css();
        case 'javascript': return javascript();
        case 'json':       return json();
        case 'xml':        return xml();
        default:           return [];
    }
}

function getWireComponent() {
    const el = document.querySelector('[wire\\:id]');
    return el ? window.Livewire.find(el.getAttribute('wire:id')) : null;
}

function createEditor(content, lang) {
    const host = document.getElementById('codemirror-host');
    if (!host) return;

    if (view) { view.destroy(); view = null; }

    view = new EditorView({
        state: EditorState.create({
            doc: content,
            extensions: [
                lineNumbers(),
                highlightActiveLineGutter(),
                highlightActiveLine(),
                drawSelection(),
                history(),
                indentOnInput(),
                bracketMatching(),
                foldGutter(),
                syntaxHighlighting(defaultHighlightStyle),
                oneDark,
                langCompartment.of(langExtension(lang)),
                keymap.of([...defaultKeymap, ...historyKeymap, indentWithTab]),
                EditorView.theme({
                    '&':           { height: '100%' },
                    '.cm-scroller':{ overflow: 'auto' },
                }),
                EditorView.updateListener.of(update => {
                    if (!update.docChanged) return;
                    getWireComponent()?.set('isDirty', true);
                    clearTimeout(saveDebounce);
                    saveDebounce = setTimeout(triggerSave, 1000);
                }),
            ],
        }),
        parent: host,
    });
}

function triggerSave() {
    if (!view) return;
    getWireComponent()?.call('saveFile', view.state.doc.toString());
}

// Listen for Livewire dispatched browser events
window.addEventListener('load-editor', e => {
    createEditor(e.detail.content, e.detail.language);
});

// Alpine component exposed on window (consumed by x-data="fileManager()")
window.fileManager = () => ({
    initEditor() { /* editor is loaded via load-editor event on mount */ },
    loadEditor(content, language) { createEditor(content, language); },
    save()       { triggerSave(); },
});
</script>
