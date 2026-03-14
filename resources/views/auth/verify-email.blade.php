<x-auth-layout title="Verify Email">

    <flux:heading size="lg" class="text-center mb-2">Verify your email</flux:heading>
    <p class="text-center text-sm text-zinc-500 dark:text-zinc-400 mb-6">
        Check your inbox for a verification link. Didn't get one?
    </p>

    @if (session('status') === 'verification-link-sent')
        <flux:callout variant="success" icon="check-circle" class="mb-4">
            A new verification link has been sent to your email address.
        </flux:callout>
    @endif

    <div class="space-y-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <flux:button type="submit" variant="primary" class="w-full">
                Resend verification email
            </flux:button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <flux:button type="submit" variant="ghost" class="w-full">
                Log out
            </flux:button>
        </form>
    </div>

</x-auth-layout>
