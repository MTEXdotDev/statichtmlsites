{{-- File Manager — Full-screen IDE layout --}}

@push('head')
{{-- CodeMirror 6 via CDN --}}
<script type="importmap">
{
    "imports": {
        "@codemirror/state": "https://esm.sh/@codemirror/state@6",
        "@codemirror/view": "https://esm.sh/@codemirror/view@6",
        "@codemirror/commands": "https://esm.sh/@codemirror/commands@6",
        "@codemirror/language": "https://esm.sh/@codemirror/language@6",
        "@codemirror/lang-html": "https://esm.sh/@codemirror/lang-html@6",
        "@codemirror/lang-css": "https://esm.sh/@codemirror/lang-css@6",
        "@codemirror/lang-javascript": "https://esm.sh/@codemirror/lang-javascript@6",
        "@codemirror/lang-json": "https://esm.sh/@codemirror/lang-json@6",
        "@codemirror/lang-xml": "https://esm.sh/@codemirror/lang-xml@6",
        "@codemirror/theme-one-dark": "https://esm.sh/@codemirror/theme-one-dark@6"
    }
}
</script>
@endpush

<div
    class="flex h-[calc(100vh-3.5rem)] overflow-hidden bg-[#1e1e2e] text-gray-100 text-sm"
    x-data="fileManager()"
    x-init="initEditor()"
    @keydown.ctrl.s.window.prevent="saveFromKeyboard()"
    @load-editor.window="loadEditor($event.detail.content, $event.detail.language)"
>

    {{-- ── Flash notification ───────────────────────────────────────────── --}}
    <div
        x-data="{ show: false, msg: '' }"
        @livewire:event.window="if($event.detail === 'flash') { msg = @js($flash); show = true; setTimeout(() => show = false, 2500); }"
        x-show="show"
        x-transition
        class="fixed top-4 right-4 z-50 bg-green-600 text-white text-xs px-3 py-2 rounded shadow-lg pointer-events-none"
        x-text="msg"
    ></div>

    {{-- ── Sidebar / File Tree ──────────────────────────────────────────── --}}
    <aside class="w-56 flex-shrink-0 bg-[#181825] border-r border-[#313244] flex flex-col overflow-hidden">

        {{-- Header --}}
        <div class="px-3 py-2 border-b border-[#313244] flex items-center justify-between">
            <span class="font-semibold text-xs text-[#cba6f7] truncate">{{ $page->name }}</span>
            <button wire:click="$set('showSettings', true)"
                    class="text-[#6c7086] hover:text-white transition" title="Settings">
                ⚙
            </button>
        </div>

        {{-- Breadcrumb --}}
        @if ($currentDir)
        <div class="px-3 py-1 flex items-center gap-1 text-xs text-[#6c7086]">
            <button wire:click="goUp" class="hover:text-white transition">↑ ..</button>
            <span class="truncate">/ {{ $currentDir }}</span>
        </div>
        @endif

        {{-- File list --}}
        <div class="flex-1 overflow-y-auto py-1">
            @foreach ($this->fileTree as $entry)
                @if ($entry['type'] === 'dir')
                    <button wire:click="enterDirectory('{{ $entry['path'] }}')"
                            class="w-full text-left px-3 py-1 hover:bg-[#313244] text-[#89b4fa] flex items-center gap-1.5 group">
                        <span>📁</span>
                        <span class="truncate flex-1">{{ $entry['name'] }}</span>
                        <button wire:click.stop="startRename('{{ $entry['path'] }}')"
                                class="hidden group-hover:inline text-[#6c7086] hover:text-white text-xs">✎</button>
                    </button>
                @else
                    <div class="flex items-center group hover:bg-[#313244] {{ $entry['active'] ? 'bg-[#313244]' : '' }}">
                        <button wire:click="openFile('{{ $entry['path'] }}')"
                                class="flex-1 text-left px-3 py-1 flex items-center gap-1.5 min-w-0">
                            <span>{{ $entry['isIndex'] ? '🏠' : '📄' }}</span>
                            <span class="truncate {{ $entry['isIndex'] ? 'text-[#a6e3a1] font-medium' : '' }}">{{ $entry['name'] }}</span>
                        </button>
                        <div class="hidden group-hover:flex items-center pr-1 gap-0.5 flex-shrink-0">
                            <button wire:click="startRename('{{ $entry['path'] }}')"
                                    class="text-[#6c7086] hover:text-white text-xs p-0.5">✎</button>
                            <button wire:click="deleteFile('{{ $entry['path'] }}')"
                                    onclick="return confirm('Delete {{ $entry['name'] }}?')"
                                    class="text-[#6c7086] hover:text-red-400 text-xs p-0.5">✕</button>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        {{-- Rename modal --}}
        @if ($renameTarget !== null)
        <div class="px-3 py-2 border-t border-[#313244] bg-[#1e1e2e]">
            <p class="text-xs text-[#6c7086] mb-1">Rename</p>
            <input wire:model="renameTo" type="text"
                   class="w-full bg-[#313244] text-white text-xs rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-[#cba6f7]">
            <div class="flex gap-1 mt-1">
                <button wire:click="confirmRename" class="flex-1 bg-[#cba6f7] text-[#1e1e2e] text-xs rounded py-1 font-medium">OK</button>
                <button wire:click="$set('renameTarget', null)" class="flex-1 bg-[#313244] text-xs rounded py-1">Cancel</button>
            </div>
        </div>
        @endif

        {{-- New file / folder --}}
        <div class="border-t border-[#313244] px-3 py-2 space-y-1.5">
            <div class="flex gap-1">
                <input wire:model="newFileName" type="text" placeholder="new-file.html"
                       wire:keydown.enter="createFile"
                       class="flex-1 min-w-0 bg-[#313244] text-white text-xs rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-[#cba6f7]">
                <button wire:click="createFile"
                        class="text-xs bg-[#313244] hover:bg-[#45475a] px-2 rounded transition">+F</button>
            </div>
            <div class="flex gap-1">
                <input wire:model="newFolderName" type="text" placeholder="new-folder"
                       wire:keydown.enter="createFolder"
                       class="flex-1 min-w-0 bg-[#313244] text-white text-xs rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-[#cba6f7]">
                <button wire:click="createFolder"
                        class="text-xs bg-[#313244] hover:bg-[#45475a] px-2 rounded transition">+D</button>
            </div>
        </div>

        {{-- Upload --}}
        <div class="border-t border-[#313244] px-3 py-2">
            <label class="block cursor-pointer text-center text-xs py-1.5 bg-[#313244] hover:bg-[#45475a] rounded transition">
                ⬆ Upload file
                <input type="file" wire:model="upload" class="hidden" @change="$wire.uploadFile()">
            </label>
            <div wire:loading wire:target="upload" class="text-center text-xs text-[#6c7086] mt-1">Uploading…</div>
        </div>

    </aside>

    {{-- ── Editor / Preview pane ───────────────────────────────────────── --}}
    <div class="flex-1 flex flex-col overflow-hidden">

        {{-- Toolbar --}}
        <div class="flex items-center gap-3 px-4 py-2 bg-[#181825] border-b border-[#313244]">
            <span class="text-[#6c7086] text-xs truncate max-w-xs">
                {{ $activeFile ?: 'No file open' }}
            </span>

            <div class="ml-auto flex items-center gap-2">
                @if ($isDirty)
                    <span class="text-xs text-yellow-400">● Unsaved</span>
                @endif

                @if ($this->isEditable && $activeFile)
                    <button @click="save()"
                            class="text-xs bg-[#313244] hover:bg-[#45475a] px-3 py-1 rounded transition">
                        Save <span class="opacity-50 text-[10px]">Ctrl+S</span>
                    </button>
                @endif

                <a href="{{ $page->subdomainUrl() }}" target="_blank"
                   class="text-xs bg-[#cba6f7] hover:bg-[#d6acff] text-[#1e1e2e] font-medium px-3 py-1 rounded transition">
                    Preview ↗
                </a>
            </div>
        </div>

        {{-- Editor --}}
        @if ($this->isEditable && $activeFile)
            <div id="codemirror-host" class="flex-1 overflow-hidden"></div>
        @elseif ($this->isPreviewable && $activeFile)
            @php $ext = strtolower(pathinfo($activeFile, PATHINFO_EXTENSION)); @endphp
            <div class="flex-1 flex items-center justify-center bg-[#1e1e2e] p-8">
                @if (in_array($ext, ['png','jpg','jpeg','gif','webp','svg']))
                    <img src="{{ $this->previewUrl }}" class="max-h-full max-w-full object-contain rounded shadow-lg">
                @elseif (in_array($ext, ['mp4','webm']))
                    <video src="{{ $this->previewUrl }}" controls class="max-h-full max-w-full rounded shadow-lg"></video>
                @elseif (in_array($ext, ['mp3','wav','ogg']))
                    <audio src="{{ $this->previewUrl }}" controls class="rounded shadow-lg"></audio>
                @elseif ($ext === 'pdf')
                    <iframe src="{{ $this->previewUrl }}" class="w-full h-full rounded"></iframe>
                @endif
            </div>
        @elseif ($activeFile)
            <div class="flex-1 flex items-center justify-center text-[#6c7086] text-sm">
                Binary file — not editable.
            </div>
        @else
            <div class="flex-1 flex flex-col items-center justify-center text-[#6c7086]">
                <p class="text-4xl mb-3">👈</p>
                <p>Select a file from the sidebar to edit.</p>
            </div>
        @endif

    </div>

    {{-- ── Settings slide-over ─────────────────────────────────────────── --}}
    @if ($showSettings)
    <div class="fixed inset-0 z-40 flex justify-end" @click.self="$wire.set('showSettings', false)">
        <div class="w-80 bg-[#181825] border-l border-[#313244] h-full flex flex-col shadow-2xl">
            <div class="px-5 py-4 border-b border-[#313244] flex items-center justify-between">
                <h2 class="font-semibold text-sm">Page Settings</h2>
                <button wire:click="$set('showSettings', false)" class="text-[#6c7086] hover:text-white">✕</button>
            </div>

            <div class="flex-1 px-5 py-5 space-y-4">
                <div>
                    <label class="block text-xs text-[#6c7086] mb-1">Page name</label>
                    <input wire:model="pageName" type="text"
                           class="w-full bg-[#313244] text-white text-sm rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#cba6f7]">
                    @error('pageName') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs text-[#6c7086] mb-1">Slug</label>
                    <input wire:model="pageSlug" type="text"
                           class="w-full bg-[#313244] text-white text-sm rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#cba6f7]">
                    @error('pageSlug') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    <p class="text-[10px] text-[#6c7086] mt-1">{{ config('app.base_domain') }}/<strong>{{ $pageSlug }}</strong></p>
                </div>

                <div class="flex items-center gap-3">
                    <input type="checkbox" id="isPublic" wire:model="pageIsPublic"
                           class="h-4 w-4 rounded border-gray-600 text-[#cba6f7] focus:ring-[#cba6f7]">
                    <label for="isPublic" class="text-sm">Publicly accessible</label>
                </div>
            </div>

            <div class="px-5 py-4 border-t border-[#313244]">
                <button wire:click="saveSettings"
                        class="w-full bg-[#cba6f7] hover:bg-[#d6acff] text-[#1e1e2e] font-semibold text-sm py-2 rounded-lg transition">
                    Save Settings
                </button>

                <div class="mt-3 pt-3 border-t border-[#313244]">
                    <form method="POST" action="{{ route('pages.destroy', $page->slug) }}"
                          onsubmit="return confirm('This will permanently delete all files.')">
                        @csrf @method('DELETE')
                        <button class="w-full text-xs text-red-400 hover:text-red-300 py-1.5 transition">
                            Delete this page and all files
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

{{-- ── CodeMirror 6 script ──────────────────────────────────────────────── --}}
<script type="module">
import { EditorState, Compartment } from '@codemirror/state';
import { EditorView, keymap, lineNumbers, highlightActiveLineGutter, highlightActiveLine, drawSelection } from '@codemirror/view';
import { defaultKeymap, history, historyKeymap, indentWithTab } from '@codemirror/commands';
import { indentOnInput, bracketMatching, foldGutter, syntaxHighlighting, defaultHighlightStyle } from '@codemirror/language';
import { html } from '@codemirror/lang-html';
import { css } from '@codemirror/lang-css';
import { javascript } from '@codemirror/lang-javascript';
import { json } from '@codemirror/lang-json';
import { xml } from '@codemirror/lang-xml';
import { oneDark } from '@codemirror/theme-one-dark';

const langCompartment = new Compartment();
let view = null;
let saveDebounce = null;

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

function createEditor(content, lang) {
    const host = document.getElementById('codemirror-host');
    if (!host) return;

    if (view) { view.destroy(); view = null; }

    const state = EditorState.create({
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
            EditorView.theme({ '&': { height: '100%' }, '.cm-scroller': { overflow: 'auto' } }),
            EditorView.updateListener.of(update => {
                if (update.docChanged) {
                    window.Livewire.find(
                        document.querySelector('[wire\\:id]').getAttribute('wire:id')
                    ).set('isDirty', true);

                    // Auto-save debounce
                    clearTimeout(saveDebounce);
                    saveDebounce = setTimeout(() => {
                        window.editorSave && window.editorSave();
                    }, 1000);
                }
            }),
        ],
    });

    view = new EditorView({ state, parent: host });

    // Expose save function globally
    window.editorSave = () => {
        if (!view) return;
        const content = view.state.doc.toString();
        window.Livewire.find(
            document.querySelector('[wire\\:id]').getAttribute('wire:id')
        ).call('saveFile', content);
    };
}

// Listen for Livewire event to load editor
document.addEventListener('livewire:initialized', () => {
    // Re-init editor when Livewire re-renders
    document.addEventListener('livewire:navigated', () => {});
});

window.addEventListener('load-editor', (e) => {
    createEditor(e.detail.content, e.detail.language);
});

// Alpine helpers exposed on window
window.fileManager = () => ({
    initEditor() {
        // Initial load handled by Livewire mount dispatching load-editor
    },
    loadEditor(content, language) {
        createEditor(content, language);
    },
    save() {
        window.editorSave && window.editorSave();
    },
    saveFromKeyboard() {
        window.editorSave && window.editorSave();
    },
});
</script>
