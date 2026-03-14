<x-auth-layout>
    <x-slot name="title">Log In</x-slot>
    <x-slot name="footer">
        No account? <a href="{{ route('register') }}" class="text-indigo-400 hover:underline">Register</a>
    </x-slot>

    <h1 class="text-xl font-bold text-white mb-6">Welcome back</h1>

    @if (session('status'))
        <div class="mb-4 text-sm text-green-400">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-xs font-medium text-gray-400 mb-1" for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   required autofocus autocomplete="username"
                   class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2.5 text-sm
                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                          placeholder-gray-600 @error('email') border-red-500 @enderror">
            @error('email')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <div class="flex items-center justify-between mb-1">
                <label class="text-xs font-medium text-gray-400" for="password">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-xs text-indigo-400 hover:underline">
                        Forgot password?
                    </a>
                @endif
            </div>
            <input id="password" type="password" name="password"
                   required autocomplete="current-password"
                   class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2.5 text-sm
                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                          @error('password') border-red-500 @enderror">
            @error('password')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-2">
            <input id="remember" type="checkbox" name="remember"
                   class="rounded border-gray-600 bg-gray-800 text-indigo-500 focus:ring-indigo-500">
            <label for="remember" class="text-sm text-gray-400">Remember me</label>
        </div>

        <button type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold
                       py-2.5 rounded-lg transition text-sm mt-2">
            Log In
        </button>
    </form>
</x-auth-layout>
