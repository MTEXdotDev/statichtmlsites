<x-auth-layout>
    <x-slot name="title">Forgot Password</x-slot>
    <x-slot name="footer">
        <a href="{{ route('login') }}" class="text-indigo-400 hover:underline">← Back to login</a>
    </x-slot>

    <h1 class="text-xl font-bold text-white mb-2">Reset your password</h1>
    <p class="text-sm text-gray-400 mb-6">
        Enter your email and we'll send you a reset link.
    </p>

    @if (session('status'))
        <div class="mb-4 text-sm text-green-400 bg-green-900/30 border border-green-800 rounded-lg px-3 py-2">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-xs font-medium text-gray-400 mb-1" for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   required autofocus
                   class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2.5 text-sm
                          focus:outline-none focus:ring-2 focus:ring-indigo-500
                          @error('email') border-red-500 @enderror">
            @error('email')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold
                       py-2.5 rounded-lg transition text-sm">
            Send Reset Link
        </button>
    </form>
</x-auth-layout>
