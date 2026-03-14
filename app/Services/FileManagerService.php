<?php

namespace App\Services;

use App\Models\Page;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class FileManagerService
{
    // Editable in the code editor
    private const EDITABLE_EXT = ['html', 'css', 'js', 'json', 'txt', 'xml', 'svg', 'md'];

    // Allowed uploads
    private const BINARY_EXT = [
        'mp4', 'mp3', 'wav', 'ogg', 'png', 'jpg', 'jpeg', 'gif',
        'webp', 'webm', 'pdf', 'ico', 'woff', 'woff2',
    ];

    // ── Tree ─────────────────────────────────────────────────────────────────

    /**
     * Return the file tree as a nested array suitable for JSON.
     */
    public function tree(Page $page): array
    {
        $root = $page->diskPath();
        return $this->buildTree($root, $root);
    }

    private function buildTree(string $directory, string $root): array
    {
        $items = [];

        $dirs  = Storage::directories($directory);
        $files = Storage::files($directory);

        foreach ($dirs as $dir) {
            $name     = basename($dir);
            $relative = $this->relative($dir, $root);
            $items[]  = [
                'type'     => 'directory',
                'name'     => $name,
                'path'     => $relative,
                'children' => $this->buildTree($dir, $root),
            ];
        }

        foreach ($files as $file) {
            $name     = basename($file);
            $relative = $this->relative($file, $root);
            $ext      = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $items[]  = [
                'type'     => 'file',
                'name'     => $name,
                'path'     => $relative,
                'editable' => in_array($ext, self::EDITABLE_EXT, true),
                'ext'      => $ext,
                'size'     => Storage::size($file),
            ];
        }

        // Directories first, then files, both alphabetically
        usort($items, fn($a, $b) =>
            [$a['type'] === 'file' ? 1 : 0, $a['name']]
            <=>
            [$b['type'] === 'file' ? 1 : 0, $b['name']]
        );

        return $items;
    }

    // ── Read / Write ─────────────────────────────────────────────────────────

    public function read(Page $page, string $relative): string
    {
        $path = $this->resolve($page, $relative);
        $this->guard($page, $path);

        if (! Storage::exists($path)) {
            throw new RuntimeException("File not found: {$relative}");
        }

        return Storage::get($path);
    }

    public function save(Page $page, string $relative, string $content): void
    {
        $path = $this->resolve($page, $relative);
        $this->guard($page, $path);
        Storage::put($path, $content);
    }

    public function createFile(Page $page, string $relative): void
    {
        $relative = $this->sanitizePath($relative);
        $path     = $this->resolve($page, $relative);
        $this->guard($page, $path);

        if (Storage::exists($path)) {
            throw new RuntimeException("File already exists: {$relative}");
        }

        Storage::put($path, '');
    }

    // ── Upload ───────────────────────────────────────────────────────────────

    public function upload(Page $page, UploadedFile $file, string $folder = ''): void
    {
        $name = $this->sanitizeName($file->getClientOriginalName());
        $ext  = strtolower($file->getClientOriginalExtension());

        $allowed = array_merge(self::EDITABLE_EXT, self::BINARY_EXT);
        if (! in_array($ext, $allowed, true)) {
            throw new RuntimeException("File type .{$ext} is not allowed.");
        }

        $folder  = $folder ? $this->sanitizePath($folder) : '';
        $diskDir = $folder
            ? $page->diskPath($folder)
            : $page->diskPath();

        $file->storeAs($diskDir, $name);
    }

    // ── Delete ───────────────────────────────────────────────────────────────

    public function delete(Page $page, string $relative): void
    {
        $path = $this->resolve($page, $relative);
        $this->guard($page, $path);

        if (Storage::directoryExists($path)) {
            Storage::deleteDirectory($path);
        } elseif (Storage::exists($path)) {
            Storage::delete($path);
        } else {
            throw new RuntimeException("Path not found: {$relative}");
        }
    }

    // ── Folder ───────────────────────────────────────────────────────────────

    public function createFolder(Page $page, string $relative): void
    {
        $relative = $this->sanitizePath($relative);
        $path     = $this->resolve($page, $relative);
        $this->guard($page, $path);
        Storage::makeDirectory($path);
    }

    // ── Rename ───────────────────────────────────────────────────────────────

    public function rename(Page $page, string $from, string $to): void
    {
        $fromPath = $this->resolve($page, $this->sanitizePath($from));
        $toPath   = $this->resolve($page, $this->sanitizePath($to));

        $this->guard($page, $fromPath);
        $this->guard($page, $toPath);

        if (Storage::directoryExists($fromPath)) {
            // Laravel doesn't have a move-directory helper; use the filesystem
            $this->moveDirectory($fromPath, $toPath);
        } elseif (Storage::exists($fromPath)) {
            Storage::move($fromPath, $toPath);
        } else {
            throw new RuntimeException("Path not found: {$from}");
        }
    }

    private function moveDirectory(string $from, string $to): void
    {
        Storage::makeDirectory($to);

        foreach (Storage::allFiles($from) as $file) {
            $relative = Str::after($file, $from . '/');
            Storage::move($file, $to . '/' . $relative);
        }

        Storage::deleteDirectory($from);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function resolve(Page $page, string $relative): string
    {
        return $page->diskPath(ltrim($relative, '/'));
    }

    private function relative(string $full, string $root): string
    {
        return ltrim(Str::after($full, $root), '/');
    }

    /**
     * Prevent path traversal attacks.
     */
    private function guard(Page $page, string $diskPath): void
    {
        $root = realpath(Storage::path($page->diskPath()));
        $real = realpath(Storage::path($diskPath))
              ?: Storage::path($diskPath); // file may not exist yet

        if ($root && ! str_starts_with($real, $root)) {
            throw new RuntimeException('Path traversal detected.');
        }
    }

    private function sanitizeName(string $name): string
    {
        // Keep extension, slugify the stem
        $ext  = pathinfo($name, PATHINFO_EXTENSION);
        $stem = pathinfo($name, PATHINFO_FILENAME);
        $safe = preg_replace('/[^a-zA-Z0-9\-_]/', '-', $stem);
        return $ext ? "{$safe}.{$ext}" : $safe;
    }

    private function sanitizePath(string $path): string
    {
        // Explode on slashes, sanitize each segment, rejoin
        $segments = array_filter(explode('/', str_replace('\\', '/', $path)));
        $safe     = array_map(fn($s) => preg_replace('/[^a-zA-Z0-9\-_.]/', '-', $s), $segments);
        return implode('/', $safe);
    }
}
