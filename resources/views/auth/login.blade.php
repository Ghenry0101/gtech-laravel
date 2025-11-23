<x-guest-layout>
    @if (session('status'))
        <div class="mb-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-8">
        @csrf

        <div class="space-y-2">
            <label for="email" class="text-xs font-semibold uppercase text-gray-900">Email</label>
            <div class="border-b border-gray-300 transition focus-within:border-gray-900">
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="Masukkan email kamu"
                    class="w-full border-none bg-transparent px-0 py-3 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-0" />
            </div>
            @error('email')
                <p class="text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div class="space-y-2">
            <label for="password" class="text-xs font-semibold uppercase text-gray-900">Password</label>
            <div class="border-b border-gray-300 transition focus-within:border-gray-900">
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="Masukkan password kamu"
                    class="w-full border-none bg-transparent px-0 py-3 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-0" />
            </div>
            @error('password')
                <p class="text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        @if (Route::has('password.request'))
            <div class="text-right text-xs font-semibold uppercase text-gray-500">
                <a href="{{ route('password.request') }}" class="text-gray-700 hover:text-gray-900">Forgot password?</a>
            </div>
        @endif

        <button type="submit" class="mt-4 w-full rounded-md bg-gray-900 py-4 text-center text-sm font-semibold uppercase text-white shadow-[0_15px_35px_rgba(0,0,0,0.25)] transition hover:bg-black">
            Sign In
        </button>

        <p class="text-center text-sm text-gray-500">
            Belum punya akun?
            <a href="{{ route('register') }}" class="font-semibold text-gray-900">Sign Up</a>
        </p>
    </form>
</x-guest-layout>
