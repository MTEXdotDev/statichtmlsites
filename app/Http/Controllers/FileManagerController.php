<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\FileManagerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FileManagerController extends Controller
{
    public function __construct(private readonly FileManagerService $fm) {}

    // ── View ─────────────────────────────────────────────────────────────────

    public function index(Request $request, string $slug): View
    {
        $page = $this->authorizedPage($request, $slug);
        return view('pages.manager', compact('page'));
    }

    // ── API endpoints ─────────────────────────────────────────────────────────

    /** List all files/folders as a tree */
    public function list(Request $request, string $slug): JsonResponse
    {
        $page = $this->authorizedPage($request, $slug);
        return response()->json($this->fm->tree($page));
    }

    /** Read a single file's content */
    public function read(Request $request, string $slug): JsonResponse
    {
        $page = $this->authorizedPage($request, $slug);
        $path = $request->query('path', '');

        return response()->json([
            'content' => $this->fm->read($page, $path),
            'path'    => $path,
        ]);
    }

    /** Save a text file */
    public function save(Request $request, string $slug): JsonResponse
    {
        $page = $this->authorizedPage($request, $slug);
        $data = $request->validate([
            'path'    => ['required', 'string'],
            'content' => ['required', 'string'],
        ]);

        $this->fm->save($page, $data['path'], $data['content']);
        return response()->json(['ok' => true]);
    }

    /** Create a new blank text file */
    public function create(Request $request, string $slug): JsonResponse
    {
        $page = $this->authorizedPage($request, $slug);
        $data = $request->validate([
            'path' => ['required', 'string'],
        ]);

        $this->fm->createFile($page, $data['path']);
        return response()->json(['ok' => true, 'tree' => $this->fm->tree($page)]);
    }

    /** Upload binary or text file(s) */
    public function upload(Request $request, string $slug): JsonResponse
    {
        $page = $this->authorizedPage($request, $slug);
        $maxMb = (int) config('filesystems.max_upload_mb', 50);

        $request->validate([
            'file'   => ["required", "file", "max:{$maxMb}000"],
            'folder' => ['nullable', 'string'],
        ]);

        $this->fm->upload($page, $request->file('file'), $request->input('folder', ''));
        return response()->json(['ok' => true, 'tree' => $this->fm->tree($page)]);
    }

    /** Delete a file or directory */
    public function delete(Request $request, string $slug): JsonResponse
    {
        $page = $this->authorizedPage($request, $slug);
        $data = $request->validate([
            'path' => ['required', 'string'],
        ]);

        $this->fm->delete($page, $data['path']);
        return response()->json(['ok' => true, 'tree' => $this->fm->tree($page)]);
    }

    /** Create a folder */
    public function createFolder(Request $request, string $slug): JsonResponse
    {
        $page = $this->authorizedPage($request, $slug);
        $data = $request->validate([
            'path' => ['required', 'string'],
        ]);

        $this->fm->createFolder($page, $data['path']);
        return response()->json(['ok' => true, 'tree' => $this->fm->tree($page)]);
    }

    /** Rename a file or folder */
    public function rename(Request $request, string $slug): JsonResponse
    {
        $page = $this->authorizedPage($request, $slug);
        $data = $request->validate([
            'from' => ['required', 'string'],
            'to'   => ['required', 'string'],
        ]);

        $this->fm->rename($page, $data['from'], $data['to']);
        return response()->json(['ok' => true, 'tree' => $this->fm->tree($page)]);
    }

    // ── Shared ────────────────────────────────────────────────────────────────

    private function authorizedPage(Request $request, string $slug): Page
    {
        return $request->user()->pages()->where('slug', $slug)->firstOrFail();
    }
}
