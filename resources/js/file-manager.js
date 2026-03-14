/**
 * file-manager.js
 * Handles: CodeMirror 6 editor, file tree (Alpine), save/load, uploads, binary preview.
 */

import { EditorView, keymap, lineNumbers, highlightActiveLine } from '@codemirror/view';
import { EditorState }                                           from '@codemirror/state';
import { defaultKeymap, history, historyKeymap }                from '@codemirror/commands';
import { indentOnInput, bracketMatching }                       from '@codemirror/language';
import { html }                                                  from '@codemirror/lang-html';
import { css }                                                   from '@codemirror/lang-css';
import { javascript }                                            from '@codemirror/lang-javascript';
import { json }                                                  from '@codemirror/lang-json';
import { xml }                                                   from '@codemirror/lang-xml';
import { oneDark }                                               from '@codemirror/theme-one-dark';
import Alpine                                                    from 'alpinejs';

// ── Globals ───────────────────────────────────────────────────────────────────

const SLUG        = window.PAGE_SLUG;
const CSRF        = window.CSRF_TOKEN;
const BASE        = `/pages/${SLUG}`;

const EDITABLE    = new Set(['html','css','js','json','txt','xml','svg','md']);
const IMG_EXT     = new Set(['png','jpg','jpeg','gif','webp','svg','ico']);
const VIDEO_EXT   = new Set(['mp4','webm','ogg']);
const AUDIO_EXT   = new Set(['mp3','wav','ogg']);

// ── CodeMirror setup ──────────────────────────────────────────────────────────

let editorView   = null;
let currentFile  = null;
let saveTimer    = null;
let isDirty      = false;

function langExtension(ext) {
    switch (ext) {
        case 'html': return html();
        case 'css':  return css();
        case 'js':   return javascript();
        case 'json': return json();
        case 'xml':
        case 'svg':  return xml();
        default:     return [];
    }
}

function createEditor(content, ext) {
    if (editorView) {
        editorView.destroy();
        editorView = null;
    }

    const updateListener = EditorView.updateListener.of(update => {
        if (update.docChanged) {
            markDirty();
        }
    });

    const saveCmd = {
        key: 'Mod-s',
        run() { saveCurrentFile(); return true; }
    };

    editorView = new EditorView({
        state: EditorState.create({
            doc: content,
            extensions: [
                oneDark,
                lineNumbers(),
                highlightActiveLine(),
                history(),
                indentOnInput(),
                bracketMatching(),
                keymap.of([saveCmd, ...defaultKeymap, ...historyKeymap]),
                langExtension(ext),
                updateListener,
                EditorView.theme({ '&': { height: '100%' } }),
            ],
        }),
        parent: document.getElementById('cm-editor'),
    });
}

function markDirty() {
    isDirty = true;
    setStatus('Unsaved changes');

    clearTimeout(saveTimer);
    saveTimer = setTimeout(() => {
        if (isDirty) saveCurrentFile();
    }, 1000);
}

function setStatus(msg) {
    document.getElementById('editor-status').textContent = msg;
}

// ── File open / save ──────────────────────────────────────────────────────────

async function openFile(path, name) {
    const ext = name.split('.').pop().toLowerCase();

    // Show/hide panels
    document.getElementById('editor-empty').classList.add('hidden');
    document.getElementById('binary-preview').classList.add('hidden');
    document.getElementById('cm-editor').classList.remove('hidden');
    document.getElementById('btn-save').classList.add('hidden');
    document.getElementById('editor-filename').textContent = path;

    if (EDITABLE.has(ext)) {
        // Text file – load into editor
        try {
            const res  = await api(`${BASE}/files/read?path=${encodeURIComponent(path)}`);
            const data = await res.json();
            createEditor(data.content, ext);
            currentFile = path;
            isDirty     = false;
            setStatus('Saved');
            document.getElementById('btn-save').classList.remove('hidden');
        } catch (e) {
            setStatus('Error loading file');
        }
        return;
    }

    // Binary file – show preview
    document.getElementById('cm-editor').classList.add('hidden');
    document.getElementById('binary-preview').classList.remove('hidden');
    showBinaryPreview(path, name, ext);
}

function showBinaryPreview(path, name, ext) {
    const url = `/${SLUG}/${path}`;
    const img   = document.getElementById('preview-img');
    const video = document.getElementById('preview-video');
    const audio = document.getElementById('preview-audio');
    const file  = document.getElementById('preview-file');

    [img, video, audio, file].forEach(el => el.classList.add('hidden'));

    if (IMG_EXT.has(ext)) {
        img.src = url;
        img.classList.remove('hidden');
    } else if (VIDEO_EXT.has(ext)) {
        video.src = url;
        video.classList.remove('hidden');
    } else if (AUDIO_EXT.has(ext)) {
        audio.src = url;
        audio.classList.remove('hidden');
    } else {
        document.getElementById('preview-file-name').textContent = name;
        file.classList.remove('hidden');
    }
}

async function saveCurrentFile() {
    if (!currentFile || !editorView) return;

    setStatus('Saving…');
    try {
        await api(`${BASE}/files`, {
            method: 'PUT',
            body: JSON.stringify({ path: currentFile, content: editorView.state.doc.toString() }),
        });
        isDirty = false;
        setStatus('Saved ✓');
    } catch (e) {
        setStatus('Save failed!');
    }
}

document.getElementById('btn-save')?.addEventListener('click', saveCurrentFile);

// ── Utility fetch wrapper ─────────────────────────────────────────────────────

function api(url, opts = {}) {
    return fetch(url, {
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept':       'application/json',
            ...(opts.headers || {}),
        },
        ...opts,
    });
}

// ── Settings panel toggle ─────────────────────────────────────────────────────

window.togglePanel = function(name) {
    const el = document.getElementById(`panel-${name}`);
    el.classList.toggle('hidden');
};

// ── Alpine file tree component ────────────────────────────────────────────────

Alpine.data('fileTree', (slug) => ({
    tree:    [],
    loading: true,
    slug,

    async init() {
        await this.refresh();
    },

    async refresh() {
        this.loading = true;
        const res    = await api(`${BASE}/files`);
        const data   = await res.json();
        this.tree    = data;
        this.loading = false;
    },

    openFile(path, name) {
        openFile(path, name);
    },

    async promptNewFile() {
        const name = prompt('File name (e.g. about.html):');
        if (!name) return;

        await api(`${BASE}/files/create`, {
            method: 'POST',
            body: JSON.stringify({ path: name }),
        });
        await this.refresh();
        openFile(name, name);
    },

    async promptNewFolder() {
        const name = prompt('Folder name:');
        if (!name) return;

        await api(`${BASE}/files/folder`, {
            method: 'POST',
            body: JSON.stringify({ path: name }),
        });
        await this.refresh();
    },

    async uploadFiles(event) {
        const files = Array.from(event.target.files);
        for (const file of files) {
            const fd = new FormData();
            fd.append('file', file);
            fd.append('_token', CSRF);

            await fetch(`${BASE}/files`, { method: 'POST', body: fd });
        }
        await this.refresh();
        event.target.value = '';
    },

    async deleteItem(path, type) {
        const label = type === 'directory' ? 'folder and all its contents' : 'file';
        if (!confirm(`Delete this ${label}?`)) return;

        await api(`${BASE}/files`, {
            method: 'DELETE',
            body: JSON.stringify({ path }),
        });
        await this.refresh();
    },

    async renameItem(path, currentName) {
        const newName = prompt('New name:', currentName);
        if (!newName || newName === currentName) return;

        const dir  = path.includes('/') ? path.rsplit('/', 1)[0] + '/' : '';
        const to   = dir + newName;

        await api(`${BASE}/files/rename`, {
            method: 'PUT',
            body: JSON.stringify({ from: path, to }),
        });
        await this.refresh();
    },

    renderItem(item, depth) {
        const indent  = depth * 14;
        const isIndex = item.name === 'index.html';
        const icon    = item.type === 'directory' ? '📁' : fileIcon(item.ext);

        const highlight = isIndex
            ? 'text-yellow-300 font-semibold'
            : 'text-gray-300 hover:text-white';

        if (item.type === 'directory') {
            let children = '';
            if (item.children && item.children.length) {
                children = item.children
                    .map(c => this.renderItem(c, depth + 1))
                    .join('');
            }
            return `
                <div>
                    <div class="flex items-center group px-2 py-0.5 hover:bg-gray-700 cursor-default"
                         style="padding-left:${8 + indent}px">
                        <span class="mr-1.5 text-xs">${icon}</span>
                        <span class="text-xs text-gray-400 flex-1 truncate">${item.name}</span>
                        <span class="hidden group-hover:flex gap-1">
                            <button onclick="Alpine.evaluate(document.querySelector('[x-data]'), 'renameItem(&quot;${item.path}&quot;, &quot;${item.name}&quot;)')"
                                    class="text-gray-500 hover:text-white text-xs px-1">✎</button>
                            <button onclick="Alpine.evaluate(document.querySelector('[x-data]'), 'deleteItem(&quot;${item.path}&quot;, &quot;directory&quot;)')"
                                    class="text-gray-500 hover:text-red-400 text-xs px-1">✕</button>
                        </span>
                    </div>
                    <div>${children}</div>
                </div>`;
        }

        return `
            <div class="flex items-center group px-2 py-0.5 hover:bg-gray-700 cursor-pointer"
                 style="padding-left:${8 + indent}px"
                 onclick="window._fileTree_openFile('${item.path}', '${item.name}')">
                <span class="mr-1.5 text-xs">${icon}</span>
                <span class="text-xs ${highlight} flex-1 truncate">${item.name}</span>
                <span class="hidden group-hover:flex gap-1">
                    <button onclick="event.stopPropagation(); Alpine.evaluate(document.querySelector('[x-data]'), 'renameItem(&quot;${item.path}&quot;, &quot;${item.name}&quot;)')"
                            class="text-gray-500 hover:text-white text-xs px-1">✎</button>
                    <button onclick="event.stopPropagation(); Alpine.evaluate(document.querySelector('[x-data]'), 'deleteItem(&quot;${item.path}&quot;, &quot;file&quot;)')"
                            class="text-gray-500 hover:text-red-400 text-xs px-1">✕</button>
                </span>
            </div>`;
    },
}));

// Bridge: called from rendered HTML strings
window._fileTree_openFile = openFile;

// ── Helper: file icons ────────────────────────────────────────────────────────

function fileIcon(ext) {
    const map = {
        html: '🌐', css: '🎨', js: '⚡', json: '{}', txt: '📄',
        xml: '📋', svg: '🖼', md: '📝',
        png: '🖼', jpg: '🖼', jpeg: '🖼', gif: '🖼', webp: '🖼', ico: '🖼',
        mp4: '🎬', webm: '🎬', mp3: '🎵', wav: '🎵', ogg: '🎵',
        pdf: '📕', woff: '🔤', woff2: '🔤',
    };
    return map[ext] ?? '📄';
}

// ── Boot Alpine ───────────────────────────────────────────────────────────────
window.Alpine = Alpine;
Alpine.start();
