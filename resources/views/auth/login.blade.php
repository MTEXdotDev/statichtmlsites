<x-auth-layout title="Log In">

    <flux:heading size="lg" class="text-center mb-6">Welcome back</flux:heading>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle" class="mb-4">
            {{ session('status') }}
        </flux:callout>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <flux:field>
            <flux:label>Email address</flux:label>
            <flux:input type="email" name="email" value="{{ old('email') }}"
                        required autofocus autocomplete="username" />
            @error('email')
                <flux:error>{{ $message }}</flux:error>
            @enderror
        </flux:field>

        <flux:field>
            <div class="flex items-center justify-between">
                <flux:label>Password</flux:label>
                @if (Route::has('password.request'))
                    <flux:link href="{{ route('password.request') }}" size="sm">
                        Forgot password?
                    </flux:link>
                @endif
            </div>
            <flux:input type="password" name="password"
                        required autocomplete="current-password" />
            @error('password')
                <flux:error>{{ $message }}</flux:error>
            @enderror
        </flux:field>

        <flux:checkbox name="remember" label="Remember me" />

        <flux:button type="submit" variant="primary" class="w-full">
            Log in
        </flux:button>
    </form>

    <p class="mt-6 text-center text-sm text-zinc-500 dark:text-zinc-400">
        No account?
        <flux:link href="{{ route('register') }}">Register</flux:link>
    </p>

</x-auth-layout>
