<x-settings-layout title="Password — Settings">

    @if (session('status') === 'password-updated')
        <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)"
             class="mb-5 text-sm text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-950/40
                    border border-green-200 dark:border-green-800 rounded-lg px-4 py-3">
            Password updated successfully.
        </div>
    @endif

    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6">
        <h2 class="text-base font-semibold text-zinc-900 dark:text-white mb-5">Change Password</h2>

        <form method="POST" action="{{ route('settings.password.update') }}" class="space-y-5">
            @csrf @method('PUT')

            @foreach ([
                ['current_password', 'Current Password', 'current-password'],
                ['password',         'New Password',     'new-password'],
                ['password_confirmation', 'Confirm New Password', 'new-password'],
            ] as [$name, $label, $autocomplete])
            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                    {{ $label }}
                </label>
                <input name="{{ $name }}" type="password" autocomplete="{{ $autocomplete }}" required
                       class="w-full px-3 py-2 text-sm rounded-lg border
                              bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100
                              border-zinc-200 dark:border-zinc-700
                              focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                @error($name, 'updatePassword')
                    <p class="text-red-500 dark:text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            @endforeach

            <button type="submit"
                    class="px-4 py-2 text-sm font-medium rounded-lg
                           bg-brand-600 hover:bg-brand-500 text-white transition">
                Update Password
            </button>
        </form>
    </div>

</x-settings-layout>
