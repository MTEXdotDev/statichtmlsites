<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PagePreviewController extends Controller
{
    /**
     * Full-screen preview with responsive device frames and toolbar.
     */
    public function show(Request $request, string $slug): View
    {
        $page = Page::where('slug', $slug)->firstOrFail();
        Gate::authorize('update', $page);

        return view('pages.preview', compact('page'));
    }
}
