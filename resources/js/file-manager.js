/**
 * file-manager.js  — loaded only on the file manager page.
 *
 * Alpine is provided by @fluxScripts (bundled with Livewire/Flux).
 * We must NOT import or start our own Alpine — Flux registers its modal
 * plugin on its own Alpine instance. We hook into it via alpine:init.
 *
 * CodeMirror 6 is tree-shaken and bundled here by Vite.
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

// ── CodeMirror state ──────────────────────────────────────────────────────────

const langCompartment = new Compartment();
let   editorView      = null;
let   saveDebounce    = null;

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
        case 'javascript': return javascript();
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
                oneDark,
                EditorView.theme({
                    '&':            { height: '100%' },
                    '.cm-scroller': { overflow: 'auto', fontFamily: "ui-monospace, 'Cascadia Code', 'JetBrains Mono', Menlo, monospace" },
                    '.cm-content':  { padding: '8px 0' },
                    '.cm-gutters':  { background: 'transparent', border: 'none' },
                }),
                lineNumbers(),
                highlightActiveLineGutter(),
                highlightActiveLine(),
                highlightSpecialChars(),
                drawSelection(),
                dropCursor(),
                history(),
                indentOnInput(),
                bracketMatching(),
                foldGutter(),
                rectangularSelection(),
                crosshairCursor(),
                syntaxHighlighting(defaultHighlightStyle, { fallback: true }),
                langCompartment.of(langExt(lang)),
                keymap.of([
                    { key: 'Mod-s', run() { triggerSave(); return true; } },
                    ...defaultKeymap,
                    ...historyKeymap,
                    indentWithTab,
                ]),
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

// ── Alpine component — registered on Flux/Livewire's Alpine instance ──────────
//
// We use alpine:init so our data component is registered BEFORE Alpine
// processes the DOM, but AFTER Flux has registered its own plugins
// (including x-flux-modal and $dispatch helpers).

document.addEventListener('alpine:init', () => {
    // Safety guard: window.Alpine is set by @fluxScripts before this fires.
    if (!window.Alpine) return;

    window.Alpine.data('fileManager', () => ({
        openDirs: [],
        ctx: { show: false, x: 0, y: 0, item: null },
        cursor: { line: 1, col: 1 },

        isOpen(path)   { return this.openDirs.includes(path); },
        toggleDir(path) {
            this.openDirs = this.isOpen(path)
                ? this.openDirs.filter(p => p !== path)
                : [...this.openDirs, path];
        },

        openContext(e, item) {
            this.ctx = { show: true, x: e.clientX, y: e.clientY, item };
        },
        closeContext() {
            this.ctx = { show: false, x: 0, y: 0, item: null };
        },

        init() {
            window.addEventListener('cm-cursor', e => { this.cursor = e.detail; });

            window.addEventListener('load-editor', e => {
                createEditor(e.detail.content, e.detail.language);
            });

            window.addEventListener('click', () => this.closeContext());
            window.addEventListener('keydown', e => {
                if (e.key === 'Escape') this.closeContext();
            });
        },

        save() { triggerSave(); },
    }));
});
