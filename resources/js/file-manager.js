/**
 * file-manager.js
 * Bundled via Vite — loaded only on the file manager page.
 * Provides: CodeMirror 6 editor, Alpine data components.
 */

import { EditorState, Compartment }               from '@codemirror/state';
import {
    EditorView, keymap, lineNumbers,
    highlightActiveLineGutter, highlightActiveLine,
    drawSelection, dropCursor, rectangularSelection,
    crosshairCursor, highlightSpecialChars,
} from '@codemirror/view';
import {
    defaultKeymap, history, historyKeymap, indentWithTab,
} from '@codemirror/commands';
import {
    indentOnInput, bracketMatching, foldGutter,
    syntaxHighlighting, defaultHighlightStyle,
} from '@codemirror/language';
import { html }        from '@codemirror/lang-html';
import { css }         from '@codemirror/lang-css';
import { javascript }  from '@codemirror/lang-javascript';
import { json }        from '@codemirror/lang-json';
import { xml }         from '@codemirror/lang-xml';
import { oneDark }     from '@codemirror/theme-one-dark';
import Alpine          from 'alpinejs';

// ── CodeMirror state ──────────────────────────────────────────────────────────

const langCompartment = new Compartment();
let   editorView      = null;
let   saveDebounce    = null;

/** Line/col published to Alpine via custom event */
function publishCursor(state) {
    const head = state.selection.main.head;
    const line = state.doc.lineAt(head);
    window.dispatchEvent(new CustomEvent('cm-cursor', {
        detail: { line: line.number, col: head - line.from + 1, total: state.doc.lines },
    }));
}

function langExt(lang) {
    switch (lang) {
        case 'html':       return html({ matchClosingTags: true, autoCloseTags: true });
        case 'css':        return css();
        case 'javascript': return javascript({ jsx: false });
        case 'json':       return json();
        case 'xml':        return xml();
        default:           return [];
    }
}

function getWire() {
    const el = document.querySelector('[wire\\:id]');
    return el ? window.Livewire?.find(el.getAttribute('wire:id')) : null;
}

function triggerSave() {
    if (!editorView) return;
    getWire()?.call('saveFile', editorView.state.doc.toString());
}

export function createEditor(content, lang) {
    const host = document.getElementById('cm-host');
    if (!host) return;

    editorView?.destroy();
    editorView = null;

    editorView = new EditorView({
        state: EditorState.create({
            doc: content,
            extensions: [
                // Appearance
                oneDark,
                EditorView.theme({
                    '&':             { height: '100%' },
                    '.cm-scroller':  { overflow: 'auto', fontFamily: "ui-monospace, 'Cascadia Code', 'JetBrains Mono', Menlo, monospace" },
                    '.cm-content':   { padding: '8px 0' },
                    '.cm-gutters':   { background: 'transparent', border: 'none' },
                }),
                // Gutter & highlights
                lineNumbers(),
                highlightActiveLineGutter(),
                highlightActiveLine(),
                highlightSpecialChars(),
                drawSelection(),
                dropCursor(),
                // Editing
                history(),
                indentOnInput(),
                bracketMatching(),
                foldGutter(),
                rectangularSelection(),
                crosshairCursor(),
                syntaxHighlighting(defaultHighlightStyle, { fallback: true }),
                // Language
                langCompartment.of(langExt(lang)),
                // Keybindings
                keymap.of([
                    { key: 'Mod-s', run() { triggerSave(); return true; } },
                    ...defaultKeymap,
                    ...historyKeymap,
                    indentWithTab,
                ]),
                // Listeners
                EditorView.updateListener.of(update => {
                    if (update.selectionSet) publishCursor(update.state);
                    if (!update.docChanged) return;
                    getWire()?.set('isDirty', true);
                    clearTimeout(saveDebounce);
                    saveDebounce = setTimeout(triggerSave, 1200);
                }),
            ],
        }),
        parent: host,
    });

    publishCursor(editorView.state);
}

// ── Alpine components ─────────────────────────────────────────────────────────

/**
 * fileManager() — root Alpine data for the manager page.
 */
window.fileManager = () => ({
    // Open folder paths (array for Alpine reactivity)
    openDirs: [],

    // Context menu state
    ctx: { show: false, x: 0, y: 0, item: null },

    // Cursor info (updated by CodeMirror)
    cursor: { line: 1, col: 1 },

    isOpen(path) {
        return this.openDirs.includes(path);
    },

    toggleDir(path) {
        if (this.isOpen(path)) {
            this.openDirs = this.openDirs.filter(p => p !== path);
        } else {
            this.openDirs = [...this.openDirs, path];
        }
    },

    openAllDirs(paths) {
        this.openDirs = [...new Set([...this.openDirs, ...paths])];
    },

    openContext(e, item) {
        this.ctx = { show: true, x: e.clientX, y: e.clientY, item };
    },

    closeContext() {
        this.ctx = { show: false, x: 0, y: 0, item: null };
    },

    init() {
        // Listen for CodeMirror cursor events
        window.addEventListener('cm-cursor', e => {
            this.cursor = e.detail;
        });

        // Listen for Livewire editor load event
        window.addEventListener('load-editor', e => {
            createEditor(e.detail.content, e.detail.language);
        });

        // Close context menu on outside click
        window.addEventListener('click', () => this.closeContext());
        window.addEventListener('keydown', e => {
            if (e.key === 'Escape') this.closeContext();
        });
    },

    save() {
        triggerSave();
    },
});

// ── Bootstrap ─────────────────────────────────────────────────────────────────

window.Alpine = Alpine;
Alpine.start();
