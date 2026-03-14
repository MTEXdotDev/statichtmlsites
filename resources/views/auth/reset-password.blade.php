<x-auth-layout>
    <x-slot name="title">Reset Password</x-slot>

    <h1 class="text-xl font-bold text-white mb-6">Choose a new password</h1>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label class="block text-xs font-medium text-gray-400 mb-1" for="email">Email</label>
            <input id="email" type="email" name="email"
                   value="{{ old('email', $request->email) }}"
                   required autofocus autocomplete="username"
                   class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2.5 text-sm
                          focus:outline-none focus:ring-2 focus:ring-indigo-500
                          @error('email') border-red-500 @enderror">
            @error('email')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-400 mb-1" for="password">New Password</label>
            <input id="password" type="password" name="password"
                   required autocomplete="new-password"
                   class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2.5 text-sm
                          focus:outline-none focus:ring-2 focus:ring-indigo-500
                          @error('password') border-red-500 @enderror">
            @error('password')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-400 mb-1" for="password_confirmation">
                Confirm Password
            </label>
            <input id="password_confirmation" type="password" name="password_confirmation"
                   required autocomplete="new-password"
                   class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2.5 text-sm
                          focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <button type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold
                       py-2.5 rounded-lg transition text-sm">
            Reset Password
        </button>
    </form>
</x-auth-layout>
