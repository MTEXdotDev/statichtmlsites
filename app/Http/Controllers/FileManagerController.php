<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\FileManagerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

/**
 * REST/JSON API for the file manager.
 * The primary UI is the Livewire\FileManager component; this controller
 * exists for programmatic / JS access and is also used by the manager page.
 */
class FileManagerController extends Controller
{
    public function __construct(private readonly FileManagerService $fm) {}

    // ── View ─────────────────────────────────────────────────────────────────

    public function index(Request $request, string $slug): View
    {
        $page = $this->ownedPage($request, $slug);
        return view('pages.manager', compact('page'));
    }

    // ── JSON endpoints ────────────────────────────────────────────────────────

    public function list(Request $request, string $slug): JsonResponse
    {
        return $this->run(fn () => $this->fm->tree($this->ownedPage($request, $slug)));
    }

    public function read(Request $request, string $slug): JsonResponse
    {
        return $this->run(function () use ($request, $slug) {
            $page = $this->ownedPage($request, $slug);
            $path = $request->query('path', '');
            return ['content' => $this->fm->read($page, $path), 'path' => $path];
        });
    }

    public function save(Request $request, string $slug): JsonResponse
    {
        return $this->run(function () use ($request, $slug) {
            $data = $request->validate(['path' => 'required|string', 'content' => 'required|string']);
            $this->fm->save($this->ownedPage($request, $slug), $data['path'], $data['content']);
            return ['ok' => true];
        });
    }

    public function create(Request $request, string $slug): JsonResponse
    {
        return $this->run(function () use ($request, $slug) {
            $data = $request->validate(['path' => 'required|string']);
            $page = $this->ownedPage($request, $slug);
            $this->fm->createFile($page, $data['path']);
            return ['ok' => true, 'tree' => $this->fm->tree($page)];
        });
    }

    public function upload(Request $request, string $slug): JsonResponse
    {
        return $this->run(function () use ($request, $slug) {
            $maxMb = (int) config('filesystems.max_upload_mb', 50);
            $request->validate([
                'file'   => "required|file|max:{$maxMb}000",
                'folder' => 'nullable|string',
            ]);
            $page = $this->ownedPage($request, $slug);
            $this->fm->upload($page, $request->file('file'), $request->input('folder', ''));
            return ['ok' => true, 'tree' => $this->fm->tree($page)];
        });
    }

    public function delete(Request $request, string $slug): JsonResponse
    {
        return $this->run(function () use ($request, $slug) {
            $data = $request->validate(['path' => 'required|string']);
            $page = $this->ownedPage($request, $slug);
            $this->fm->delete($page, $data['path']);
            return ['ok' => true, 'tree' => $this->fm->tree($page)];
        });
    }

    public function createFolder(Request $request, string $slug): JsonResponse
    {
        return $this->run(function () use ($request, $slug) {
            $data = $request->validate(['path' => 'required|string']);
            $page = $this->ownedPage($request, $slug);
            $this->fm->createFolder($page, $data['path']);
            return ['ok' => true, 'tree' => $this->fm->tree($page)];
        });
    }

    public function rename(Request $request, string $slug): JsonResponse
    {
        return $this->run(function () use ($request, $slug) {
            $data = $request->validate(['from' => 'required|string', 'to' => 'required|string']);
            $page = $this->ownedPage($request, $slug);
            $this->fm->rename($page, $data['from'], $data['to']);
            return ['ok' => true, 'tree' => $this->fm->tree($page)];
        });
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function ownedPage(Request $request, string $slug): Page
    {
        return $request->user()->pages()->where('slug', $slug)->firstOrFail();
    }

    private function run(callable $fn): JsonResponse
    {
        try {
            $result = $fn();
            return response()->json($result);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
