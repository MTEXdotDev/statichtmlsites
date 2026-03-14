<x-app-layout title="Dashboard — {{ config('app.name') }}">

    <div class="max-w-6xl mx-auto px-6 py-10">

        <div class="flex items-center justify-between mb-8">
            <flux:heading size="xl">Your Pages</flux:heading>
            <flux:button href="{{ route('pages.create') }}" variant="primary" icon="plus">
                New Page
            </flux:button>
        </div>

        @if ($pages->isEmpty())
            <div class="text-center py-28">
                <p class="text-5xl mb-4">📄</p>
                <flux:heading size="lg" class="text-zinc-400">No pages yet.</flux:heading>
                <flux:link href="{{ route('pages.create') }}" class="mt-3 inline-block">
                    Create your first page →
                </flux:link>
            </div>
        @else
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($pages as $page)
                    <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-zinc-200
                                dark:border-zinc-700 shadow-xs hover:shadow-md transition p-5 flex flex-col">

                        <div class="flex items-start justify-between mb-3">
                            <div class="min-w-0">
                                <flux:heading size="sm" class="truncate">{{ $page->name }}</flux:heading>
                                <code class="text-xs text-indigo-500 bg-indigo-50 dark:bg-indigo-950
                                             dark:text-indigo-300 px-1.5 py-0.5 rounded mt-1 inline-block">
                                    {{ $page->slug }}
                                </code>
                            </div>
                            <flux:badge
                                variant="{{ $page->is_public ? 'lime' : 'zinc' }}"
                                size="sm"
                                class="ml-2 shrink-0">
                                {{ $page->is_public ? 'Public' : 'Private' }}
                            </flux:badge>
                        </div>

                        <p class="text-xs text-zinc-400 mt-auto mb-4 pt-3
                                  border-t border-zinc-100 dark:border-zinc-700">
                            Updated {{ $page->updated_at->diffForHumans() }}
                        </p>

                        <div class="flex gap-2">
                            <flux:button href="{{ route('pages.manager', $page->slug) }}"
                                         variant="filled" size="sm" class="flex-1">
                                Edit
                            </flux:button>
                            <flux:button href="{{ $page->subdomainUrl() }}"
                                         target="_blank" rel="noopener"
                                         variant="ghost" size="sm" class="flex-1"
                                         icon-trailing="arrow-top-right-on-square">
                                Preview
                            </flux:button>
                            <form method="POST" action="{{ route('pages.destroy', $page->slug) }}"
                                  onsubmit="return confirm('Delete \'{{ addslashes($page->name) }}\'?\nThis cannot be undone.')">
                                @csrf @method('DELETE')
                                <flux:button type="submit" variant="danger" size="sm" icon="trash" />
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>

</x-app-layout>
