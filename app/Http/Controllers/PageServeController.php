<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PageServeController extends Controller
{
    /**
     * Serve a static file from a page's storage root.
     *
     * Works for both subdomain access ({slug}.statichtmlsites.mtex.dev)
     * and path-based access (statichtmlsites.mtex.dev/{slug}/...).
     */
    public function serve(Request $request, string $slug, string $path = ''): Response|BinaryFileResponse|StreamedResponse
    {
        $page = Page::where('slug', $slug)->firstOrFail();

        // Authorization: private pages require auth
        if (! $page->is_public && ! auth()->check()) {
            abort(403, 'This page is private.');
        }

        // Normalise path and resolve default document
        $filePath = $this->resolvePath($slug, $path);

        if (! Storage::exists($filePath)) {
            abort(404, 'File not found.');
        }

        // Security: prevent path traversal
        $this->guardTraversal($slug, $filePath);

        $absolutePath = Storage::path($filePath);
        $mime = $this->detectMime($filePath);
        $ext  = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // Inject <base> tag into HTML responses
        if ($ext === 'html') {
            $html = Storage::get($filePath);
            $html = $this->injectBase($html, $page, $request, $slug);

            return response($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
        }

        // Stream binary / text files as-is
        return response()->file($absolutePath, ['Content-Type' => $mime]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function resolvePath(string $slug, string $path): string
    {
        $path = ltrim($path, '/');

        if ($path === '' || $path === '/') {
            $path = 'index.html';
        }

        // If path is a directory (no extension), try index.html inside it
        if (! pathinfo($path, PATHINFO_EXTENSION)) {
            $candidate = rtrim($path, '/') . '/index.html';
            $diskPath  = "pages/{$slug}/{$candidate}";
            if (Storage::exists($diskPath)) {
                return $diskPath;
            }
        }

        return "pages/{$slug}/{$path}";
    }

    private function guardTraversal(string $slug, string $filePath): void
    {
        $root = realpath(Storage::path("pages/{$slug}"));
        $real = realpath(Storage::path($filePath));

        if ($root && $real && ! str_starts_with($real, $root)) {
            abort(403, 'Path traversal detected.');
        }
    }

    private function injectBase(string $html, Page $page, Request $request, string $slug): string
    {
        // Detect subdomain access
        $host      = $request->getHost();
        $baseDomain = config('app.base_domain', 'statichtmlsites.mtex.dev');
        $isSubdomain = str_ends_with($host, ".{$baseDomain}");

        $baseUrl = $isSubdomain
            ? $page->subdomainUrl()
            : $page->pathUrl();

        $baseTag = "<base href=\"{$baseUrl}\">\n";

        // Inject as first child of <head>
        if (preg_match('/<head([^>]*)>/i', $html)) {
            return preg_replace('/<head([^>]*)>/i', "<head$1>\n{$baseTag}", $html, 1);
        }

        // No <head> – prepend at top
        return $baseTag . $html;
    }

    private function detectMime(string $path): string
    {
        $map = [
            'html'  => 'text/html',
            'css'   => 'text/css',
            'js'    => 'application/javascript',
            'json'  => 'application/json',
            'xml'   => 'application/xml',
            'svg'   => 'image/svg+xml',
            'txt'   => 'text/plain',
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
        ];

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return $map[$ext] ?? 'application/octet-stream';
    }
}
