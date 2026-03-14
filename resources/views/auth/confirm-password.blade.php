<x-auth-layout>
    <x-slot name="title">Confirm Password</x-slot>

    <h1 class="text-xl font-bold text-white mb-2">Confirm your password</h1>
    <p class="text-sm text-gray-400 mb-6">
        This area requires you to confirm your password before continuing.
    </p>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-xs font-medium text-gray-400 mb-1" for="password">Password</label>
            <input id="password" type="password" name="password"
                   required autocomplete="current-password"
                   class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2.5 text-sm
                          focus:outline-none focus:ring-2 focus:ring-indigo-500
                          @error('password') border-red-500 @enderror">
            @error('password')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold
                       py-2.5 rounded-lg transition text-sm">
            Confirm
        </button>
    </form>
</x-auth-layout>
