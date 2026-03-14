<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PageServeController extends Controller
{
    /**
     * Serve a static file from the pages disk.
     * Called by both subdomain and path-based routes.
     */
    public function serve(Request $request, string $slug, string $path = ''): Response|BinaryFileResponse
    {
        $page = Page::where('slug', $slug)->firstOrFail();

        if (! $page->is_public && ! auth()->check()) {
            abort(403, 'This page is private.');
        }

        // Resolve the disk path and guard against traversal
        $diskPath = $this->resolveDiskPath($slug, $path);
        $this->guardTraversal($slug, $diskPath);

        $disk = Storage::disk('pages');

        if (! $disk->exists($diskPath)) {
            abort(404, 'File not found.');
        }

        $ext  = strtolower(pathinfo($diskPath, PATHINFO_EXTENSION));
        $mime = $this->detectMime($ext);

        // Inject <base> tag into HTML responses
        if ($ext === 'html') {
            $html = $disk->get($diskPath);
            $html = $this->injectBase($html, $page, $request);
            return response($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
        }

        return response()->file($disk->path($diskPath), ['Content-Type' => $mime]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Resolve a path within the page's slug directory on the pages disk.
     * Handles directory index resolution.
     */
    private function resolveDiskPath(string $slug, string $path): string
    {
        $path = ltrim($path, '/');

        if ($path === '' || $path === '/') {
            return "{$slug}/index.html";
        }

        // No extension → try index.html inside that directory
        if (! pathinfo($path, PATHINFO_EXTENSION)) {
            $candidate = "{$slug}/" . rtrim($path, '/') . '/index.html';
            if (Storage::disk('pages')->exists($candidate)) {
                return $candidate;
            }
        }

        return "{$slug}/{$path}";
    }

    /**
     * Prevent path traversal by checking the real path stays inside the slug dir.
     */
    private function guardTraversal(string $slug, string $diskPath): void
    {
        $disk     = Storage::disk('pages');
        $root     = realpath($disk->path($slug));
        $realFile = realpath($disk->path($diskPath));

        // realpath returns false for non-existent files – only check when both resolve
        if ($root && $realFile && ! str_starts_with($realFile, $root)) {
            abort(403, 'Path traversal detected.');
        }
    }

    /**
     * Inject <base href="..."> as the first child of <head>.
     * Detects subdomain vs path-based access automatically.
     */
    private function injectBase(string $html, Page $page, Request $request): string
    {
        $host       = $request->getHost();
        $baseDomain = config('app.base_domain', 'statichtmlsites.mtex.dev');
        $isSubdomain = str_ends_with($host, ".{$baseDomain}");

        $baseUrl = $isSubdomain ? $page->subdomainUrl() : $page->pathUrl();
        $baseTag = "<base href=\"{$baseUrl}\">\n";

        if (preg_match('/<head([^>]*)>/i', $html)) {
            return preg_replace('/<head([^>]*)>/i', "<head$1>\n{$baseTag}", $html, 1);
        }

        return $baseTag . $html;
    }

    private function detectMime(string $ext): string
    {
        return [
            'html'  => 'text/html',
            'css'   => 'text/css',
            'js'    => 'application/javascript',
            'mjs'   => 'application/javascript',
            'json'  => 'application/json',
            'xml'   => 'application/xml',
            'svg'   => 'image/svg+xml',
            'txt'   => 'text/plain',
            'md'    => 'text/plain',
            'png'   => 'image/png',
            'jpg'   => 'image/jpeg',
            'jpeg'  => 'image/jpeg',
            'gif'   => 'image/gif',
            'webp'  => 'image/webp',
            'ico'   => 'image/x-icon',
            'mp4'   => 'video/mp4',
            'webm'  => 'video/webm',
            'mp3'   => 'audio/mpeg',
            'wav'   => 'audio/wav',
            'ogg'   => 'audio/ogg',
            'pdf'   => 'application/pdf',
            'woff'  => 'font/woff',
            'woff2' => 'font/woff2',
        ][$ext] ?? 'application/octet-stream';
    }
}
