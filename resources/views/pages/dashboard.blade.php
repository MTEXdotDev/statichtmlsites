<x-app-layout>
    <x-slot name="title">Dashboard — StaticHTMLSites</x-slot>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">My Pages</h1>
        <a href="{{ route('pages.create') }}"
           class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition text-sm font-medium">
            + New Page
        </a>
    </div>

    @if ($pages->isEmpty())
        <div class="text-center py-20 text-gray-400">
            <p class="text-4xl mb-3">📄</p>
            <p class="text-lg">No pages yet. Create your first one!</p>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($pages as $page)
                <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md transition">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h2 class="font-semibold text-gray-900 truncate">{{ $page->name }}</h2>
                            <code class="text-xs text-indigo-500 bg-indigo-50 px-1.5 py-0.5 rounded mt-1 inline-block">
                                {{ $page->slug }}
                            </code>
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $page->is_public ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $page->is_public ? 'Public' : 'Private' }}
                        </span>
                    </div>

                    <div class="text-xs text-gray-400 mb-4">
                        Updated {{ $page->updated_at->diffForHumans() }}
                    </div>

                    <div class="flex gap-2 text-sm">
                        <a href="{{ route('pages.manager', $page->slug) }}"
                           class="flex-1 text-center bg-indigo-50 text-indigo-700 px-3 py-1.5 rounded-md hover:bg-indigo-100 transition font-medium">
                            Edit
                        </a>
                        <a href="{{ $page->subdomainUrl() }}" target="_blank"
                           class="flex-1 text-center bg-gray-50 text-gray-700 px-3 py-1.5 rounded-md hover:bg-gray-100 transition font-medium">
                            Preview ↗
                        </a>
                        <form method="POST" action="{{ route('pages.destroy', $page->slug) }}"
                              onsubmit="return confirm('Delete \'{{ $page->name }}\'? This cannot be undone.')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="bg-red-50 text-red-600 px-3 py-1.5 rounded-md hover:bg-red-100 transition font-medium">
                                ✕
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-app-layout>
