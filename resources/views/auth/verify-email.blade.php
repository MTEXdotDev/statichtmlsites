<x-auth-layout>
    <x-slot name="title">Verify Email</x-slot>

    <h1 class="text-xl font-bold text-white mb-3">Verify your email</h1>
    <p class="text-sm text-gray-400 mb-6">
        Thanks for signing up! Please check your email for a verification link.
        If you didn't receive one, we can send another.
    </p>

    @if (session('status') === 'verification-link-sent')
        <div class="mb-5 text-sm text-green-400 bg-green-900/30 border border-green-800 rounded-lg px-3 py-2">
            A new verification link has been sent to your email address.
        </div>
    @endif

    <div class="flex flex-col gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold
                           py-2.5 rounded-lg transition text-sm">
                Resend Verification Email
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="w-full border border-gray-700 text-gray-400 hover:text-white
                           py-2.5 rounded-lg transition text-sm">
                Log Out
            </button>
        </form>
    </div>
</x-auth-layout>
