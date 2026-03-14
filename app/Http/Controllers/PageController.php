<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PageController extends Controller
{
    public function create(): View
    {
        return view('pages.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:60', 'regex:/^[a-z0-9\-_]+$/'],
        ]);

        $slug = $data['slug'] ?? Page::makeUniqueSlug($data['name']);

        // Ensure uniqueness even if user provided a slug
        if (Page::where('slug', $slug)->exists()) {
            return back()
                ->withInput()
                ->withErrors(['slug' => 'This slug is already taken.']);
        }

        $page = $request->user()->pages()->create([
            'name'      => $data['name'],
            'slug'      => $slug,
            'is_public' => true,
        ]);

        return redirect()
            ->route('pages.manager', $page->slug)
            ->with('success', "Page \"{$page->name}\" created!");
    }

    public function updateSettings(Request $request, string $slug): RedirectResponse
    {
        $page = $request->user()->pages()->where('slug', $slug)->firstOrFail();

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'slug'      => ['required', 'string', 'max:60', 'regex:/^[a-z0-9\-_]+$/'],
            'is_public' => ['boolean'],
        ]);

        // Slug uniqueness check (excluding current page)
        if (
            $data['slug'] !== $page->slug &&
            Page::where('slug', $data['slug'])->exists()
        ) {
            return back()->withErrors(['slug' => 'This slug is already taken.']);
        }

        $page->update($data);

        return redirect()
            ->route('pages.manager', $page->slug)
            ->with('success', 'Settings updated.');
    }

    public function destroy(Request $request, string $slug): RedirectResponse
    {
        $page = $request->user()->pages()->where('slug', $slug)->firstOrFail();
        $page->delete();

        return redirect()
            ->route('dashboard')
            ->with('success', "Page \"{$page->name}\" deleted.");
    }
}
