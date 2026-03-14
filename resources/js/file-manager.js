/**
 * file-manager.js — loaded only on the file manager page.
 *
 * Alpine is provided by @livewireScripts (Livewire 4 bundles Alpine 3).
 * We register our data component via alpine:init — that hooks into
 * Livewire's Alpine instance after it is created but before DOM init.
 *
 * Modals are plain Alpine x-show panels — no Flux Pro required.
 */

import { EditorState, Compartment }              from '@codemirror/state';
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
import { html }       from '@codemirror/lang-html';
import { css }        from '@codemirror/lang-css';
import { javascript } from '@codemirror/lang-javascript';
import { json }       from '@codemirror/lang-json';
import { xml }        from '@codemirror/lang-xml';
import { oneDark }    from '@codemirror/theme-one-dark';

// ── CodeMirror ────────────────────────────────────────────────────────────────

const langCompartment = new Compartment();
let   editorView      = null;
let   saveDebounce    = null;

function publishCursor(state) {
    const head = state.selection.main.head;
    const line = state.doc.lineAt(head);
    window.dispatchEvent(new CustomEvent('cm-cursor', {
        detail: { line: line.number, col: head - line.from + 1 },
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

function createEditor(content, lang) {
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
                    '.cm-scroller': { overflow: 'auto', fontFamily: "ui-monospace,'Cascadia Code','JetBrains Mono',Menlo,monospace" },
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
                    // Dispatch to Alpine only — never call $wire.set() here.
                    // $wire.set() triggers a full Livewire re-render which destroys #cm-host.
                    window.dispatchEvent(new CustomEvent('cm-dirty'));
                    clearTimeout(saveDebounce);
                    saveDebounce = setTimeout(triggerSave, 1200);
                }),
            ],
        }),
        parent: host,
    });

    publishCursor(editorView.state);
}

// ── Alpine component ──────────────────────────────────────────────────────────

document.addEventListener('alpine:init', () => {
    window.Alpine.data('fileManager', () => ({
        // Modal state — name of currently open modal, or null
        modal: null,

        // File tree — which directories are expanded
        openDirs: [],

        // Context-menu state
        ctx: { show: false, x: 0, y: 0, item: null },

        // Status-bar cursor info
        cursor: { line: 1, col: 1 },

        // Drag-and-drop state
        dragOver: false,

        // Dirty state — tracked purely in Alpine, never via $wire.set()
        dirty: false,

        // ── Modal helpers ────────────────────────────────────────────────
        openModal(name)  { this.modal = name; },
        closeModal()     { this.modal = null; },
        isModal(name)    { return this.modal === name; },

        // ── File-tree helpers ────────────────────────────────────────────
        isOpen(path)     { return this.openDirs.includes(path); },
        toggleDir(path)  {
            this.openDirs = this.isOpen(path)
                ? this.openDirs.filter(p => p !== path)
                : [...this.openDirs, path];
        },

        // ── Context menu ─────────────────────────────────────────────────
        openCtx(e, item) {
            e.preventDefault();
            this.ctx = { show: true, x: e.clientX, y: e.clientY, item };
        },
        closeCtx()       { this.ctx.show = false; },

        // ── Drag-and-drop upload ─────────────────────────────────────────
        onDrop(e) {
            this.dragOver = false;
            const files = Array.from(e.dataTransfer?.files ?? []);
            if (!files.length) return;
            files.forEach(file => {
                const wire = getWire();
                if (wire) wire.upload('upload', file, () => {}, () => {}, () => {});
            });
        },

        // ── Init ─────────────────────────────────────────────────────────
        init() {
            // CodeMirror cursor position
            window.addEventListener('cm-cursor', e => { this.cursor = e.detail; });

            // Editor content changed — mark dirty in Alpine only (never via $wire.set)
            window.addEventListener('cm-dirty', () => { this.dirty = true; });

            // File opened — reset dirty and create editor
            window.addEventListener('load-editor', e => {
                this.dirty = false;
                createEditor(e.detail.content, e.detail.language);
            });

            // Livewire saved successfully — reset dirty
            window.addEventListener('file-saved', () => { this.dirty = false; });

            // Livewire → Alpine modal control
            window.addEventListener('open-modal',  e => this.openModal(e.detail?.name));
            window.addEventListener('close-modal', () => this.closeModal());

            // Close context menu on click/Escape
            window.addEventListener('click', () => this.closeCtx());
            window.addEventListener('keydown', e => {
                if (e.key === 'Escape') { this.closeCtx(); this.closeModal(); }
            });
        },

        save() { triggerSave(); },
    }));
});
