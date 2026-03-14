<x-app-layout title="New Page — {{ config('app.name') }}">

    <div class="max-w-lg mx-auto px-6 py-10">

        <flux:heading size="xl" class="mb-6">Create New Page</flux:heading>

        <form method="POST" action="{{ route('pages.store') }}" class="space-y-5">
            @csrf

            <flux:field>
                <flux:label>Page Name</flux:label>
                <flux:input type="text" name="name" value="{{ old('name') }}"
                            required autofocus placeholder="My Awesome Site" />
                @error('name')
                    <flux:error>{{ $message }}</flux:error>
                @enderror
            </flux:field>

            <flux:field>
                <flux:label>
                    Slug
                    <flux:badge variant="zinc" size="sm" class="ml-1">optional</flux:badge>
                </flux:label>
                <div class="flex items-center">
                    <span class="text-sm text-zinc-400 dark:text-zinc-500 mr-1 shrink-0">
                        {{ config('app.base_domain') }}/
                    </span>
                    <flux:input type="text" name="slug" value="{{ old('slug') }}"
                                placeholder="my-awesome-site" class="flex-1" />
                </div>
                <flux:description>Leave blank to auto-generate from the page name.</flux:description>
                @error('slug')
                    <flux:error>{{ $message }}</flux:error>
                @enderror
            </flux:field>

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary">
                    Create Page
                </flux:button>
                <flux:button href="{{ route('dashboard') }}" variant="ghost">
                    Cancel
                </flux:button>
            </div>
        </form>

    </div>

</x-app-layout>
