<?php

namespace App\Services;

use App\Models\Page;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * All file operations use Storage::disk('pages').
 * Paths passed in/out are always relative to the page root:
 *   e.g.  "index.html"  or  "assets/logo.png"
 */
class FileManagerService
{
    private const EDITABLE_EXT = ['html', 'css', 'js', 'json', 'txt', 'xml', 'svg', 'md'];

    private const ALLOWED_EXT  = [
        'html', 'css', 'js', 'json', 'txt', 'xml', 'svg', 'md',
        'mp4', 'mp3', 'wav', 'ogg', 'png', 'jpg', 'jpeg', 'gif',
        'webp', 'webm', 'pdf', 'ico', 'woff', 'woff2',
    ];

    private function disk(): \Illuminate\Contracts\Filesystem\Filesystem
    {
        return Storage::disk('pages');
    }

    // ── Tree ─────────────────────────────────────────────────────────────────

    public function tree(Page $page): array
    {
        return $this->buildTree($page->slug, $page->slug);
    }

    private function buildTree(string $dir, string $root): array
    {
        $items = [];

        foreach ($this->disk()->directories($dir) as $d) {
            $name     = basename($d);
            $relative = $this->relative($d, $root);
            $items[]  = [
                'type'     => 'directory',
                'name'     => $name,
                'path'     => $relative,
                'children' => $this->buildTree($d, $root),
            ];
        }

        foreach ($this->disk()->files($dir) as $f) {
            $name     = basename($f);
            $relative = $this->relative($f, $root);
            $ext      = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $items[]  = [
                'type'     => 'file',
                'name'     => $name,
                'path'     => $relative,
                'editable' => in_array($ext, self::EDITABLE_EXT, true),
                'ext'      => $ext,
                'size'     => $this->disk()->size($f),
            ];
        }

        usort($items, fn ($a, $b) =>
            [$a['type'] === 'file' ? 1 : 0, $a['name']]
            <=>
            [$b['type'] === 'file' ? 1 : 0, $b['name']]
        );

        return $items;
    }

    // ── Read / Write ──────────────────────────────────────────────────────────

    public function read(Page $page, string $relative): string
    {
        $path = $page->storagePath($relative);
        $this->guard($page, $relative);

        if (! $this->disk()->exists($path)) {
            throw new RuntimeException("File not found: {$relative}");
        }

        return $this->disk()->get($path);
    }

    public function save(Page $page, string $relative, string $content): void
    {
        $this->guard($page, $relative);
        $this->disk()->put($page->storagePath($relative), $content);
    }

    public function createFile(Page $page, string $relative): void
    {
        $relative = $this->sanitizePath($relative);
        $path     = $page->storagePath($relative);
        $this->guard($page, $relative);

        if ($this->disk()->exists($path)) {
            throw new RuntimeException("File already exists: {$relative}");
        }

        $this->disk()->put($path, '');
    }

    // ── Upload ────────────────────────────────────────────────────────────────

    public function upload(Page $page, UploadedFile $file, string $folder = ''): void
    {
        $name = $this->sanitizeName($file->getClientOriginalName());
        $ext  = strtolower($file->getClientOriginalExtension());

        if (! in_array($ext, self::ALLOWED_EXT, true)) {
            throw new RuntimeException("File type .{$ext} is not allowed.");
        }

        $folder  = $folder ? $this->sanitizePath($folder) : '';
        $diskDir = $folder
            ? $page->storagePath($folder)
            : $page->slug;

        $this->disk()->putFileAs($diskDir, $file, $name);
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    public function delete(Page $page, string $relative): void
    {
        $path = $page->storagePath($relative);
        $this->guard($page, $relative);

        if ($this->disk()->directoryExists($path)) {
            $this->disk()->deleteDirectory($path);
        } elseif ($this->disk()->exists($path)) {
            $this->disk()->delete($path);
        } else {
            throw new RuntimeException("Path not found: {$relative}");
        }
    }

    // ── Folder ────────────────────────────────────────────────────────────────

    public function createFolder(Page $page, string $relative): void
    {
        $relative = $this->sanitizePath($relative);
        $this->guard($page, $relative);
        $this->disk()->makeDirectory($page->storagePath($relative));
    }

    // ── Rename ────────────────────────────────────────────────────────────────

    public function rename(Page $page, string $from, string $to): void
    {
        $from = $this->sanitizePath($from);
        $to   = $this->sanitizePath($to);

        $this->guard($page, $from);
        $this->guard($page, $to);

        $fromPath = $page->storagePath($from);
        $toPath   = $page->storagePath($to);

        if ($this->disk()->directoryExists($fromPath)) {
            $this->moveDirectory($fromPath, $toPath);
        } elseif ($this->disk()->exists($fromPath)) {
            $this->disk()->move($fromPath, $toPath);
        } else {
            throw new RuntimeException("Path not found: {$from}");
        }
    }

    private function moveDirectory(string $from, string $to): void
    {
        $this->disk()->makeDirectory($to);

        foreach ($this->disk()->allFiles($from) as $file) {
            $relative = Str::after($file, $from . '/');
            $this->disk()->move($file, $to . '/' . $relative);
        }

        $this->disk()->deleteDirectory($from);
    }

    // ── Security ──────────────────────────────────────────────────────────────

    /**
     * Prevent path traversal. 'relative' must not escape the page slug directory.
     */
    private function guard(Page $page, string $relative): void
    {
        if (str_contains($relative, '..') || str_starts_with($relative, '/')) {
            throw new RuntimeException('Path traversal detected.');
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function relative(string $full, string $root): string
    {
        return ltrim(Str::after($full, $root . '/'), '/');
    }

    private function sanitizeName(string $name): string
    {
        $ext  = pathinfo($name, PATHINFO_EXTENSION);
        $stem = pathinfo($name, PATHINFO_FILENAME);
        $safe = preg_replace('/[^a-zA-Z0-9\-_]/', '-', $stem);
        return $ext ? "{$safe}.{$ext}" : $safe;
    }

    private function sanitizePath(string $path): string
    {
        $segments = array_filter(explode('/', str_replace('\\', '/', $path)));
        $safe     = array_map(fn ($s) => preg_replace('/[^a-zA-Z0-9\-_.]/', '-', $s), $segments);
        return implode('/', $safe);
    }
}
