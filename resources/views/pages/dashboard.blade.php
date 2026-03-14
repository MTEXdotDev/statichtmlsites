<x-app-layout>
    <x-slot name="title">Dashboard — {{ config('app.name') }}</x-slot>

    <div class="max-w-6xl mx-auto px-6 py-10">

        <div class="flex items-center justify-between mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Your Pages</h1>
            <a href="{{ route('pages.create') }}"
               class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Page
            </a>
        </div>

        @if ($pages->isEmpty())
            <div class="text-center py-28 text-gray-400">
                <p class="text-5xl mb-4">📄</p>
                <p class="text-lg font-medium">No pages yet.</p>
                <a href="{{ route('pages.create') }}"
                   class="mt-4 inline-block text-indigo-600 hover:underline text-sm">
                    Create your first page →
                </a>
            </div>
        @else
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($pages as $page)
                    <article class="bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-md transition p-5 flex flex-col">
                        <div class="flex items-start justify-between mb-2">
                            <div class="min-w-0">
                                <h2 class="font-semibold text-gray-900 truncate">{{ $page->name }}</h2>
                                <code class="text-xs text-indigo-500 bg-indigo-50 px-1.5 py-0.5 rounded mt-1 inline-block">
                                    {{ $page->slug }}
                                </code>
                            </div>
                            <span class="ml-2 shrink-0 text-xs px-2 py-0.5 rounded-full font-medium
                                {{ $page->is_public ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $page->is_public ? 'Public' : 'Private' }}
                            </span>
                        </div>

                        <p class="text-xs text-gray-400 mt-auto mb-4 pt-3 border-t border-gray-100">
                            Updated {{ $page->updated_at->diffForHumans() }}
                        </p>

                        <div class="flex gap-2 text-sm">
                            <a href="{{ route('pages.manager', $page->slug) }}"
                               class="flex-1 text-center bg-indigo-50 text-indigo-700 px-3 py-1.5 rounded-lg hover:bg-indigo-100 transition font-medium">
                                Edit
                            </a>
                            <a href="{{ $page->subdomainUrl() }}" target="_blank" rel="noopener"
                               class="flex-1 text-center bg-gray-50 text-gray-600 px-3 py-1.5 rounded-lg hover:bg-gray-100 transition font-medium">
                                Preview ↗
                            </a>
                            <form method="POST" action="{{ route('pages.destroy', $page->slug) }}"
                                  onsubmit="return confirm('Delete \'{{ addslashes($page->name) }}\'?\nThis cannot be undone.')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="bg-red-50 text-red-500 px-3 py-1.5 rounded-lg hover:bg-red-100 transition font-medium">
                                    ✕
                                </button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
