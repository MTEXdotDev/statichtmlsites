<?php

namespace App\Livewire;

use App\Models\Page;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class FileManager extends Component
{
    use WithFileUploads;

    // ─── State ────────────────────────────────────────────────────────────────

    public Page   $page;
    public string $activeFile    = '';
    public string $fileContent   = '';
    public bool   $isDirty       = false;
    public string $newFileName   = '';
    public string $newFolderName = '';
    public string $currentDir    = '';   // relative to page root
    public ?string $renameTarget = null;
    public string $renameTo      = '';
    public $upload                = null; // Livewire temp upload

    // Settings panel
    public string $pageName      = '';
    public string $pageSlug      = '';
    public bool   $pageIsPublic  = false;
    public bool   $showSettings  = false;

    // Notification
    public string $flash         = '';

    // ─── Lifecycle ────────────────────────────────────────────────────────────

    public function mount(string $slug): void
    {
        $this->page = Page::where('slug', $slug)->firstOrFail();
        Gate::authorize('update', $this->page);

        $this->page->ensureStorageExists();

        $this->pageName     = $this->page->name;
        $this->pageSlug     = $this->page->slug;
        $this->pageIsPublic = $this->page->is_public;

        // Auto-open index.html if it exists
        $indexPath = 'index.html';
        if (Storage::disk('pages')->exists($this->page->storagePath($indexPath))) {
            $this->openFile($indexPath);
        }
    }

    // ─── Computed ─────────────────────────────────────────────────────────────

    #[Computed]
    public function fileTree(): array
    {
        $disk   = Storage::disk('pages');
        $root   = $this->page->storagePath($this->currentDir);
        $prefix = $this->page->storagePath() . '/';

        $entries = [];

        // Directories
        foreach ($disk->directories($root) as $dir) {
            $entries[] = [
                'type' => 'dir',
                'name' => basename($dir),
                'path' => ltrim(str_replace($prefix, '', $dir), '/'),
            ];
        }

        // Files
        foreach ($disk->files($root) as $file) {
            $rel = ltrim(str_replace($prefix, '', $file), '/');
            $entries[] = [
                'type'    => 'file',
                'name'    => basename($file),
                'path'    => $rel,
                'active'  => $rel === $this->activeFile,
                'isIndex' => basename($file) === 'index.html',
            ];
        }

        usort($entries, fn ($a, $b) => [$a['type'] === 'file' ? 1 : 0, $a['name']] <=> [$b['type'] === 'file' ? 1 : 0, $b['name']]);

        return $entries;
    }

    #[Computed]
    public function editorLanguage(): string
    {
        return match (strtolower(pathinfo($this->activeFile, PATHINFO_EXTENSION))) {
            'html', 'htm' => 'html',
            'css'         => 'css',
            'js'          => 'javascript',
            'json'        => 'json',
            'xml'         => 'xml',
            'svg'         => 'xml',
            'txt'         => 'text',
            default       => 'text',
        };
    }

    #[Computed]
    public function isEditable(): bool
    {
        $editable = ['html', 'htm', 'css', 'js', 'json', 'txt', 'xml', 'svg'];
        return in_array(strtolower(pathinfo($this->activeFile, PATHINFO_EXTENSION)), $editable);
    }

    #[Computed]
    public function isPreviewable(): bool
    {
        $media = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'mp4', 'webm', 'mp3', 'wav', 'ogg', 'pdf'];
        return in_array(strtolower(pathinfo($this->activeFile, PATHINFO_EXTENSION)), $media);
    }

    #[Computed]
    public function previewUrl(): string
    {
        return $this->page->subdomainUrl() . $this->activeFile;
    }

    // ─── File operations ──────────────────────────────────────────────────────

    public function openFile(string $path): void
    {
        $this->ensureSafe($path);
        $disk = Storage::disk('pages');
        $full = $this->page->storagePath($path);

        if (! $disk->exists($full)) {
            $this->flash = "File not found: {$path}";
            return;
        }

        $this->activeFile  = $path;
        $this->fileContent = $disk->get($full);
        $this->isDirty     = false;

        $this->dispatch('load-editor', content: $this->fileContent, language: $this->editorLanguage);
    }

    public function saveFile(string $content): void
    {
        if ($this->activeFile === '') return;

        $this->ensureSafe($this->activeFile);
        Storage::disk('pages')->put(
            $this->page->storagePath($this->activeFile),
            $content
        );

        $this->fileContent = $content;
        $this->isDirty     = false;
        $this->flash       = 'Saved ✓';
    }

    public function createFile(): void
    {
        $name = $this->sanitiseName($this->newFileName);
        if ($name === '') {
            $this->flash = 'File name cannot be empty.';
            return;
        }

        $relative = $this->currentDir ? "{$this->currentDir}/{$name}" : $name;
        $full     = $this->page->storagePath($relative);

        if (Storage::disk('pages')->exists($full)) {
            $this->flash = 'A file with that name already exists.';
            return;
        }

        Storage::disk('pages')->put($full, '');
        $this->newFileName = '';
        $this->openFile($relative);
        unset($this->fileTree);
    }

    public function createFolder(): void
    {
        $name = $this->sanitiseName($this->newFolderName);
        if ($name === '') {
            $this->flash = 'Folder name cannot be empty.';
            return;
        }

        $relative = $this->currentDir ? "{$this->currentDir}/{$name}" : $name;
        Storage::disk('pages')->makeDirectory($this->page->storagePath($relative));

        $this->newFolderName = '';
        $this->flash         = "Folder '{$name}' created.";
        unset($this->fileTree);
    }

    public function deleteFile(string $path): void
    {
        $this->ensureSafe($path);
        Storage::disk('pages')->delete($this->page->storagePath($path));

        if ($this->activeFile === $path) {
            $this->activeFile  = '';
            $this->fileContent = '';
        }

        $this->flash = "Deleted {$path}.";
        unset($this->fileTree);
    }

    public function startRename(string $path): void
    {
        $this->renameTarget = $path;
        $this->renameTo     = basename($path);
    }

    public function confirmRename(): void
    {
        if ($this->renameTarget === null) return;

        $this->ensureSafe($this->renameTarget);
        $newName = $this->sanitiseName($this->renameTo);
        if ($newName === '') return;

        $dir     = dirname($this->renameTarget);
        $newPath = ($dir === '.' ? '' : $dir . '/') . $newName;

        Storage::disk('pages')->move(
            $this->page->storagePath($this->renameTarget),
            $this->page->storagePath($newPath)
        );

        if ($this->activeFile === $this->renameTarget) {
            $this->openFile($newPath);
        }

        $this->renameTarget = null;
        $this->renameTo     = '';
        unset($this->fileTree);
    }

    public function enterDirectory(string $path): void
    {
        $this->currentDir = $path;
        unset($this->fileTree);
    }

    public function goUp(): void
    {
        $this->currentDir = dirname($this->currentDir) === '.' ? '' : dirname($this->currentDir);
        unset($this->fileTree);
    }

    // ─── Upload ───────────────────────────────────────────────────────────────

    public function uploadFile(): void
    {
        $this->validate([
            'upload' => 'required|file|max:' . (config('app.max_upload_mb', 50) * 1024),
        ]);

        $originalName = $this->sanitiseName($this->upload->getClientOriginalName());
        $relative     = $this->currentDir ? "{$this->currentDir}/{$originalName}" : $originalName;

        Storage::disk('pages')->putFileAs(
            $this->page->storagePath($this->currentDir ?: ''),
            $this->upload,
            $originalName
        );

        $this->upload = null;
        $this->flash  = "Uploaded {$originalName}.";
        unset($this->fileTree);
    }

    // ─── Page settings ────────────────────────────────────────────────────────

    public function saveSettings(): void
    {
        $data = $this->validate([
            'pageName'     => 'required|string|max:255',
            'pageSlug'     => "required|string|max:100|regex:/^[a-z0-9\-]+$/|unique:pages,slug,{$this->page->id}",
            'pageIsPublic' => 'boolean',
        ]);

        $oldSlug = $this->page->slug;
        $newSlug = $this->pageSlug;

        if ($oldSlug !== $newSlug) {
            Storage::disk('pages')->move($oldSlug, $newSlug);
        }

        $this->page->update([
            'name'      => $this->pageName,
            'slug'      => $newSlug,
            'is_public' => $this->pageIsPublic,
        ]);

        $this->page->refresh();
        $this->showSettings = false;
        $this->flash        = 'Settings saved.';

        if ($oldSlug !== $newSlug) {
            $this->redirect(route('pages.manager', $newSlug));
        }
    }

    // ─── Security ─────────────────────────────────────────────────────────────

    private function ensureSafe(string $path): void
    {
        if (str_contains($path, '..') || str_starts_with($path, '/')) {
            abort(400, 'Invalid path.');
        }
    }

    private function sanitiseName(string $name): string
    {
        // Allow alphanumeric, dash, underscore, dot
        return preg_replace('/[^a-zA-Z0-9\-_.]/', '-', trim($name));
    }

    // ─── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.file-manager')
            ->layout('layouts.app', ['title' => "Manager — {$this->page->name}"]);
    }
}
