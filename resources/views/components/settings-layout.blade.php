@props(['title' => 'Settings'])
<x-app-layout :title="$title">
    <div class="max-w-4xl mx-auto px-6 py-10">
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-white mb-8">Settings</h1>

        <div class="flex gap-8">
            <aside class="w-48 shrink-0">
                <nav class="space-y-1">
                    @foreach ([
                        ['settings.profile',  'Profile',  'user-circle'],
                        ['settings.password', 'Password', 'lock-closed'],
                    ] as [$route, $label, $icon])
                    <a href="{{ route($route) }}"
                       class="flex items-center gap-2.5 px-3 py-2 text-sm rounded-lg transition
                              {{ request()->routeIs($route)
                                 ? 'bg-brand-50 dark:bg-brand-950/40 text-brand-700 dark:text-brand-300 font-medium'
                                 : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-white/8' }}">
                        <flux:icon :name="$icon" class="size-4" />
                        {{ $label }}
                    </a>
                    @endforeach
                </nav>
            </aside>

            <div class="flex-1 min-w-0">{{ $slot }}</div>
        </div>
    </div>
</x-app-layout>
