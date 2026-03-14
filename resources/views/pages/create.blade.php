<x-app-layout>
    <x-slot name="title">New Page — StaticHTMLSites</x-slot>

    <div class="max-w-lg mx-auto">
        <h1 class="text-2xl font-bold mb-6">Create New Page</h1>

        <form method="POST" action="{{ route('pages.store') }}"
              class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="name">Page Name</label>
                <input id="name" name="name" type="text" required autofocus
                       value="{{ old('name') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                       placeholder="My Awesome Site">
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="slug">
                    Slug <span class="text-gray-400 font-normal">(optional — auto-generated if blank)</span>
                </label>
                <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-indigo-400">
                    <span class="bg-gray-50 border-r border-gray-300 px-3 py-2 text-sm text-gray-400 select-none">
                        statichtmlsites.mtex.dev/
                    </span>
                    <input id="slug" name="slug" type="text"
                           value="{{ old('slug') }}"
                           class="flex-1 px-3 py-2 text-sm focus:outline-none"
                           placeholder="my-awesome-site">
                </div>
                @error('slug')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="bg-indigo-600 text-white px-5 py-2 rounded-lg hover:bg-indigo-700 transition text-sm font-medium">
                    Create Page →
                </button>
                <a href="{{ route('dashboard') }}"
                   class="px-5 py-2 rounded-lg border border-gray-300 text-sm text-gray-600 hover:bg-gray-50 transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
