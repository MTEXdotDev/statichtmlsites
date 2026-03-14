<?php

namespace App\Livewire;

use App\Models\Page;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class FileManager extends Component
{
    use WithFileUploads;

    // ─── Public state ─────────────────────────────────────────────────────────

    public Page    $page;
    public string  $activeFile    = '';
    public string  $fileContent   = '';
    public bool    $isDirty       = false;
    public string  $newFileName   = '';
    public string  $newFolderName = '';
    public string  $currentDir    = '';
    public ?string $renameTarget  = null;
    public string  $renameTo      = '';
    public mixed   $upload        = null;

    // Delete modal state
    public string $deleteTarget     = '';
    public string $deleteTargetName = '';
    public string $deleteTargetType = 'file';

    // Settings panel
    public string $pageName     = '';
    public string $pageSlug     = '';
    public bool   $pageIsPublic = false;

    // ─── Lifecycle ────────────────────────────────────────────────────────────

    public function mount(string $slug): void
    {
        $this->page = Page::where('slug', $slug)->firstOrFail();
        Gate::authorize('update', $this->page);

        $this->page->ensureStorageExists();

        $this->pageName     = $this->page->name;
        $this->pageSlug     = $this->page->slug;
        $this->pageIsPublic = $this->page->is_public;

        if ($this->disk()->exists($this->page->storagePath('index.html'))) {
            $this->openFile('index.html');
        }
    }

    // ─── Computed ─────────────────────────────────────────────────────────────

    #[Computed]
    public function fileTree(): array
    {
        $dir    = $this->page->storagePath($this->currentDir ?: '');
        $prefix = $this->page->storagePath() . '/';
        return $this->buildTree($dir, $prefix);
    }

    private function buildTree(string $dir, string $prefix): array
    {
        $entries = [];

        foreach ($this->disk()->directories($dir) as $d) {
            $entries[] = [
                'type'     => 'dir',
                'name'     => basename($d),
                'path'     => $this->relative($d, $prefix),
                'children' => $this->buildTree($d, $prefix),
            ];
        }

        foreach ($this->disk()->files($dir) as $f) {
            $rel       = $this->relative($f, $prefix);
            $entries[] = [
                'type'    => 'file',
                'name'    => basename($f),
                'path'    => $rel,
                'active'  => $rel === $this->activeFile,
                'isIndex' => basename($f) === 'index.html',
            ];
        }

        usort($entries, fn ($a, $b) =>
            [$a['type'] === 'file' ? 1 : 0, $a['name']]
            <=>
            [$b['type'] === 'file' ? 1 : 0, $b['name']]
        );

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
            'xml', 'svg'  => 'xml',
            default       => 'text',
        };
    }

    #[Computed]
    public function isEditable(): bool
    {
        return in_array(
            strtolower(pathinfo($this->activeFile, PATHINFO_EXTENSION)),
            ['html', 'htm', 'css', 'js', 'json', 'txt', 'xml', 'svg', 'md'],
            true
        );
    }

    #[Computed]
    public function isPreviewable(): bool
    {
        return in_array(
            strtolower(pathinfo($this->activeFile, PATHINFO_EXTENSION)),
            ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'ico', 'mp4', 'webm', 'mp3', 'wav', 'ogg', 'pdf'],
            true
        );
    }

    #[Computed]
    public function previewUrl(): string
    {
        return $this->page->subdomainUrl($this->activeFile);
    }

    // ─── File operations ──────────────────────────────────────────────────────

    public function openFile(string $path): void
    {
        $this->guardPath($path);
        $full = $this->page->storagePath($path);

        if (! $this->disk()->exists($full)) {
            $this->showFlash("File not found: {$path}", 'error');
            return;
        }

        $this->activeFile  = $path;
        $this->fileContent = $this->disk()->get($full) ?? '';
        $this->isDirty     = false;

        $this->dispatch('load-editor',
            content: $this->fileContent,
            language: $this->editorLanguage
        );
    }

    public function saveFile(string $content): void
    {
        if ($this->activeFile === '') return;

        $this->guardPath($this->activeFile);
        $this->disk()->put($this->page->storagePath($this->activeFile), $content);

        $this->fileContent = $content;
        $this->isDirty     = false;
        $this->showFlash('Saved ✓');
    }

    public function createFile(): void
    {
        $name = $this->sanitiseName($this->newFileName);

        if ($name === '') {
            $this->showFlash('File name cannot be empty.', 'error');
            return;
        }

        $relative = $this->buildRelative($name);
        $full     = $this->page->storagePath($relative);

        if ($this->disk()->exists($full)) {
            $this->showFlash('A file with that name already exists.', 'error');
            return;
        }

        $this->disk()->put($full, '');
        $this->newFileName = '';
        $this->openFile($relative);
        unset($this->fileTree);
    }

    /** Create file and close the modal. */
    public function createFileAndClose(): void
    {
        $name = $this->sanitiseName($this->newFileName);
        if ($name === '') { $this->showFlash('File name cannot be empty.', 'error'); return; }

        $relative = $this->buildRelative($name);
        $full     = $this->page->storagePath($relative);

        if ($this->disk()->exists($full)) {
            $this->showFlash('A file with that name already exists.', 'error');
            return;
        }

        $this->disk()->put($full, '');
        $this->newFileName = '';
        $this->dispatch('flux:modal.close', name: 'new-file');
        $this->openFile($relative);
        unset($this->fileTree);
    }

    public function createFolder(): void
    {
        $name = $this->sanitiseName($this->newFolderName);
        if ($name === '') { $this->showFlash('Folder name cannot be empty.', 'error'); return; }

        $relative = $this->buildRelative($name);
        $this->disk()->makeDirectory($this->page->storagePath($relative));

        $this->newFolderName = '';
        $this->showFlash("Folder '{$name}' created.");
        unset($this->fileTree);
    }

    /** Create folder and close the modal. */
    public function createFolderAndClose(): void
    {
        $name = $this->sanitiseName($this->newFolderName);
        if ($name === '') { $this->showFlash('Folder name cannot be empty.', 'error'); return; }

        $relative = $this->buildRelative($name);
        $this->disk()->makeDirectory($this->page->storagePath($relative));

        $this->newFolderName = '';
        $this->dispatch('flux:modal.close', name: 'new-folder');
        $this->showFlash("Folder '{$name}' created.");
        unset($this->fileTree);
    }

    /** Show delete confirmation modal. */
    public function prepareDelete(string $path, string $type = 'file'): void
    {
        $this->guardPath($path);
        $this->deleteTarget     = $path;
        $this->deleteTargetName = basename($path);
        $this->deleteTargetType = $type;
        $this->dispatch('flux:modal.show', name: 'delete-confirm');
    }

    /** Execute the pending delete after modal confirmation. */
    public function executeDelete(): void
    {
        if ($this->deleteTarget === '') return;

        $full = $this->page->storagePath($this->deleteTarget);

        if ($this->disk()->directoryExists($full)) {
            $this->disk()->deleteDirectory($full);
        } else {
            $this->disk()->delete($full);
        }

        if ($this->activeFile === $this->deleteTarget) {
            $this->activeFile  = '';
            $this->fileContent = '';
            $this->isDirty     = false;
        }

        $this->showFlash("Deleted {$this->deleteTargetName}.");
        $this->deleteTarget     = '';
        $this->deleteTargetName = '';
        $this->dispatch('flux:modal.close', name: 'delete-confirm');
        unset($this->fileTree);
    }

    /** Prepare rename (shows inline form in sidebar). */
    public function prepareRename(string $path): void
    {
        $this->guardPath($path);
        $this->renameTarget = $path;
        $this->renameTo     = basename($path);
    }

    public function confirmRename(): void
    {
        if ($this->renameTarget === null) return;

        $this->guardPath($this->renameTarget);
        $newName = $this->sanitiseName($this->renameTo);
        if ($newName === '') return;

        $dir     = dirname($this->renameTarget);
        $newPath = ($dir === '.' ? '' : $dir . '/') . $newName;

        $this->disk()->move(
            $this->page->storagePath($this->renameTarget),
            $this->page->storagePath($newPath)
        );

        if ($this->activeFile === $this->renameTarget) {
            $this->openFile($newPath);
        }

        $this->renameTarget = null;
        $this->renameTo     = '';
        $this->showFlash("Renamed to {$newName}.");
        unset($this->fileTree);
    }

    public function enterDirectory(string $path): void
    {
        $this->currentDir = $path;
        unset($this->fileTree);
    }

    public function goUp(): void
    {
        $parent           = dirname($this->currentDir);
        $this->currentDir = ($parent === '.' || $parent === '') ? '' : $parent;
        unset($this->fileTree);
    }

    // ─── Upload ───────────────────────────────────────────────────────────────

    public function uploadFile(): void
    {
        $maxKb = (int) config('filesystems.max_upload_mb', 50) * 1024;
        $this->validate(['upload' => "required|file|max:{$maxKb}"]);

        $name     = $this->sanitiseName($this->upload->getClientOriginalName());
        $storeDir = $this->page->storagePath($this->currentDir ?: '');
        $this->disk()->putFileAs($storeDir, $this->upload, $name);

        $this->upload = null;
        $this->showFlash("Uploaded {$name}.");
        $this->dispatch('flux:modal.close', name: 'upload');
        unset($this->fileTree);
    }

    // ─── Page settings ────────────────────────────────────────────────────────

    public function saveSettings(): void
    {
        $this->validate([
            'pageName'     => 'required|string|max:255',
            'pageSlug'     => "required|string|max:100|regex:/^[a-z0-9\\-]+$/|unique:pages,slug,{$this->page->id}",
            'pageIsPublic' => 'boolean',
        ]);

        $this->page->update([
            'name'      => $this->pageName,
            'slug'      => $this->pageSlug,
            'is_public' => $this->pageIsPublic,
        ]);

        $this->page->refresh();
        $this->dispatch('flux:modal.close', name: 'settings');
        $this->showFlash('Settings saved.');

        if ($this->page->wasChanged('slug')) {
            $this->redirect(route('pages.manager', $this->page->slug), navigate: true);
        }
    }

    public function deletePage(): void
    {
        $this->page->delete();
        $this->redirect(route('dashboard'), navigate: true);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function showFlash(string $message, string $type = 'success'): void
    {
        $this->dispatch('show-flash', message: $message, type: $type);
    }

    private function disk(): \Illuminate\Contracts\Filesystem\Filesystem
    {
        return Storage::disk('pages');
    }

    private function guardPath(string $path): void
    {
        if (str_contains($path, '..') || str_starts_with($path, '/')) {
            abort(400, 'Invalid path.');
        }
    }

    private function buildRelative(string $name): string
    {
        return $this->currentDir ? "{$this->currentDir}/{$name}" : $name;
    }

    private function relative(string $fullPath, string $prefix): string
    {
        return ltrim(str_replace($prefix, '', $fullPath), '/');
    }

    private function sanitiseName(string $name): string
    {
        return preg_replace('/[^a-zA-Z0-9\-_.]/', '-', trim($name));
    }

    // ─── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.file-manager');
    }
}
