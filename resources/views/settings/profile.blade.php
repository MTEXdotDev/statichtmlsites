<x-settings-layout title="Profile — Settings">

    @if (session('status') === 'profile-updated')
        <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)"
             class="mb-5 text-sm text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-950/40
                    border border-green-200 dark:border-green-800 rounded-lg px-4 py-3">
            Profile updated successfully.
        </div>
    @endif

    {{-- Profile form --}}
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 mb-5">
        <h2 class="text-base font-semibold text-zinc-900 dark:text-white mb-5">Profile Information</h2>

        <form method="POST" action="{{ route('settings.profile.update') }}" class="space-y-5">
            @csrf @method('PATCH')

            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5" for="name">
                    Name
                </label>
                <input id="name" name="name" type="text"
                       value="{{ old('name', $user->name) }}" required autocomplete="name"
                       class="w-full px-3 py-2 text-sm rounded-lg border
                              bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100
                              border-zinc-200 dark:border-zinc-700
                              focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                @error('name')
                    <p class="text-red-500 dark:text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5" for="email">
                    Email
                </label>
                <input id="email" name="email" type="email"
                       value="{{ old('email', $user->email) }}" required autocomplete="email"
                       class="w-full px-3 py-2 text-sm rounded-lg border
                              bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100
                              border-zinc-200 dark:border-zinc-700
                              focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                @error('email')
                    <p class="text-red-500 dark:text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror

                @if ($mustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div class="mt-2 text-sm text-amber-600 dark:text-amber-400">
                        Your email is unverified.
                        <form method="POST" action="{{ route('verification.send') }}" class="inline">
                            @csrf
                            <button type="submit" class="underline hover:no-underline">
                                Resend verification
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-3">
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium rounded-lg
                               bg-brand-600 hover:bg-brand-500 text-white transition">
                    Save Profile
                </button>
            </div>
        </form>
    </div>

    {{-- Appearance --}}
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 mb-5">
        <h2 class="text-base font-semibold text-zinc-900 dark:text-white mb-1">Appearance</h2>
        <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-5">Choose your preferred colour scheme.</p>

        <div x-data="{
                theme: localStorage.getItem('theme') ?? 'system',
                set(t) {
                    this.theme = t;
                    localStorage.setItem('theme', t);
                    const dark = t === 'dark' || (t === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                    const html = document.documentElement;
                    html.classList.add('transitioning');
                    dark ? html.classList.add('dark') : html.classList.remove('dark');
                    setTimeout(() => html.classList.remove('transitioning'), 300);
                }
             }"
             class="flex gap-3">
            @foreach ([
                ['light',  'Light',  'sun'],
                ['dark',   'Dark',   'moon'],
                ['system', 'System', 'computer-desktop'],
            ] as [$val, $label, $icon])
            <button
                x-on:click="set('{{ $val }}')"
                x-bind:class="theme === '{{ $val }}'
                    ? 'border-brand-500 bg-brand-50 dark:bg-brand-950/40 text-brand-700 dark:text-brand-300'
                    : 'border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-400 hover:border-zinc-300 dark:hover:border-zinc-600'"
                class="flex flex-col items-center gap-2 px-5 py-3 rounded-xl border text-sm font-medium transition">
                <flux:icon :name="$icon" class="size-5" />
                {{ $label }}
            </button>
            @endforeach
        </div>
    </div>

    {{-- Danger zone --}}
    <div class="bg-white dark:bg-zinc-900 border border-red-200 dark:border-red-900/50 rounded-2xl p-6">
        <h2 class="text-base font-semibold text-red-600 dark:text-red-400 mb-1">Delete Account</h2>
        <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-4">
            Once your account is deleted, all pages and files will be permanently removed.
        </p>

        <form method="POST" action="{{ route('settings.profile.destroy') }}"
              x-data
              x-on:submit.prevent="
                const pw = prompt('Enter your password to confirm account deletion:');
                if (pw) { $el.querySelector('input[name=password]').value = pw; $el.submit(); }
              ">
            @csrf @method('DELETE')
            <input type="hidden" name="password">
            <button type="submit"
                    class="px-4 py-2 text-sm font-medium rounded-lg
                           bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400
                           border border-red-200 dark:border-red-800
                           hover:bg-red-100 dark:hover:bg-red-900/40 transition">
                Delete Account
            </button>
        </form>
        @error('userDeletion.password')
            <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
        @enderror
    </div>

</x-settings-layout>
