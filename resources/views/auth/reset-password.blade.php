<x-auth-layout title="Reset Password">

    <flux:heading size="lg" class="text-center mb-6">Choose a new password</flux:heading>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <flux:field>
            <flux:label>Email address</flux:label>
            <flux:input type="email" name="email"
                        value="{{ old('email', $request->email) }}"
                        required autofocus autocomplete="username" />
            @error('email')
                <flux:error>{{ $message }}</flux:error>
            @enderror
        </flux:field>

        <flux:field>
            <flux:label>New password</flux:label>
            <flux:input type="password" name="password"
                        required autocomplete="new-password" />
            @error('password')
                <flux:error>{{ $message }}</flux:error>
            @enderror
        </flux:field>

        <flux:field>
            <flux:label>Confirm new password</flux:label>
            <flux:input type="password" name="password_confirmation"
                        required autocomplete="new-password" />
        </flux:field>

        <flux:button type="submit" variant="primary" class="w-full">
            Reset password
        </flux:button>
    </form>

</x-auth-layout>
