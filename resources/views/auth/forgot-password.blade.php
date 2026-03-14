<x-auth-layout title="Forgot Password">

    <flux:heading size="lg" class="text-center mb-2">Reset your password</flux:heading>
    <p class="text-center text-sm text-zinc-500 dark:text-zinc-400 mb-6">
        Enter your email and we'll send a reset link.
    </p>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle" class="mb-4">
            {{ session('status') }}
        </flux:callout>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <flux:field>
            <flux:label>Email address</flux:label>
            <flux:input type="email" name="email" value="{{ old('email') }}"
                        required autofocus />
            @error('email')
                <flux:error>{{ $message }}</flux:error>
            @enderror
        </flux:field>

        <flux:button type="submit" variant="primary" class="w-full">
            Send reset link
        </flux:button>
    </form>

    <p class="mt-6 text-center text-sm">
        <flux:link href="{{ route('login') }}">← Back to login</flux:link>
    </p>

</x-auth-layout>
