<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" class="space-y-8 ">
        @csrf

        <div class="space-y-2">
            <label for="name" class="text-xs font-semibold uppercase  text-gray-900">Username</label>
            <div class="border-b border-gray-300 transition focus-within:border-gray-900">
                <input
                    id="name"
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Masukkan username kamu"
                    class="w-full border-none bg-transparent px-0 py-3 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-0" />
            </div>
            @error('name')
                <p class="text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div class="space-y-2">
            <label for="email" class="text-xs font-semibold uppercase text-gray-900">Email</label>
            <div class="border-b border-gray-300 transition focus-within:border-gray-900">
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
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
                    autocomplete="new-password"
                    placeholder="Masukkan password kamu"
                    class="w-full border-none bg-transparent px-0 py-3 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-0" />
            </div>
            @error('password')
                <p class="text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div class="space-y-2">
            <label for="password_confirmation" class="text-xs font-semibold uppercase text-gray-900">Konfirmasi Password</label>
            <div class="border-b border-gray-300 transition focus-within:border-gray-900">
                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="Konfirmasi password kamu"
                    class="w-full border-none bg-transparent px-0 py-3 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-0" />
            </div>
            @error('password_confirmation')
                <p class="text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="mt-4 w-full rounded-[18px] bg-gray-900 py-4 text-center text-sm font-semibold uppercase  text-white shadow-[0_15px_35px_rgba(0,0,0,0.25)] transition hover:bg-black">
            Sign Up
        </button>

        <p class="text-center text-sm text-gray-500">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="font-semibold text-gray-900">Sign In</a>
        </p>
    </form>
</x-guest-layout>
