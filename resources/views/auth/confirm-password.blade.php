<x-auth-layout title="Confirm Password">

    <flux:heading size="lg" class="text-center mb-2">Confirm your password</flux:heading>
    <p class="text-center text-sm text-zinc-500 dark:text-zinc-400 mb-6">
        Please confirm your password before continuing.
    </p>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        <flux:field>
            <flux:label>Password</flux:label>
            <flux:input type="password" name="password"
                        required autocomplete="current-password" />
            @error('password')
                <flux:error>{{ $message }}</flux:error>
            @enderror
        </flux:field>

        <flux:button type="submit" variant="primary" class="w-full">
            Confirm
        </flux:button>
    </form>

</x-auth-layout>
