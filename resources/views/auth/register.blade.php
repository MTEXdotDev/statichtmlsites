<x-auth-layout title="Register">

    <flux:heading size="lg" class="text-center mb-6">Create your account</flux:heading>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <flux:field>
            <flux:label>Name</flux:label>
            <flux:input type="text" name="name" value="{{ old('name') }}"
                        required autofocus autocomplete="name" />
            @error('name')
                <flux:error>{{ $message }}</flux:error>
            @enderror
        </flux:field>

        <flux:field>
            <flux:label>Email address</flux:label>
            <flux:input type="email" name="email" value="{{ old('email') }}"
                        required autocomplete="username" />
            @error('email')
                <flux:error>{{ $message }}</flux:error>
            @enderror
        </flux:field>

        <flux:field>
            <flux:label>Password</flux:label>
            <flux:input type="password" name="password"
                        required autocomplete="new-password" />
            @error('password')
                <flux:error>{{ $message }}</flux:error>
            @enderror
        </flux:field>

        <flux:field>
            <flux:label>Confirm password</flux:label>
            <flux:input type="password" name="password_confirmation"
                        required autocomplete="new-password" />
            @error('password_confirmation')
                <flux:error>{{ $message }}</flux:error>
            @enderror
        </flux:field>

        <flux:button type="submit" variant="primary" class="w-full">
            Create account
        </flux:button>
    </form>

    <p class="mt-6 text-center text-sm text-zinc-500 dark:text-zinc-400">
        Already have an account?
        <flux:link href="{{ route('login') }}">Log in</flux:link>
    </p>

</x-auth-layout>
