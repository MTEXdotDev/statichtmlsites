<x-layouts.app title="Dashboard">

<div class="max-w-5xl mx-auto px-4 py-10">

    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-semibold text-gray-900">Your Pages</h1>
        <a href="{{ route('pages.create') }}"
           class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Page
        </a>
    </div>

    @if ($pages->isEmpty())
        <div class="text-center py-24 text-gray-400">
            <p class="text-5xl mb-4">📄</p>
            <p class="text-lg">No pages yet.</p>
            <a href="{{ route('pages.create') }}" class="mt-4 inline-block text-indigo-600 hover:underline text-sm">Create your first page →</a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($pages as $page)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition p-5 flex flex-col gap-3">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="font-semibold text-gray-900">{{ $page->name }}</h2>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $page->slug }}</p>
                    </div>
                    <span class="text-xs px-2 py-1 rounded-full {{ $page->is_public ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $page->is_public ? 'Public' : 'Private' }}
                    </span>
                </div>

                <div class="flex gap-2 mt-auto pt-2 border-t border-gray-100">
                    <a href="{{ route('pages.manager', $page->slug) }}"
                       class="flex-1 text-center text-sm bg-indigo-50 hover:bg-indigo-100 text-indigo-700 px-3 py-1.5 rounded-lg transition">
                        Manager
                    </a>
                    <a href="{{ $page->subdomainUrl() }}" target="_blank"
                       class="flex-1 text-center text-sm bg-gray-50 hover:bg-gray-100 text-gray-700 px-3 py-1.5 rounded-lg transition">
                        Preview ↗
                    </a>
                </div>

                <form method="POST" action="{{ route('pages.destroy', $page->slug) }}"
                      onsubmit="return confirm('Delete {{ $page->name }}? This cannot be undone.')">
                    @csrf @method('DELETE')
                    <button class="w-full text-xs text-red-400 hover:text-red-600 transition">Delete</button>
                </form>
            </div>
            @endforeach
        </div>
    @endif

</div>

</x-layouts.app>
