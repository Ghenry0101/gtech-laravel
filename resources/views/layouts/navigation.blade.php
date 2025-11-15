@php
    use Illuminate\Support\Facades\Route;

    $user = Auth::user();
    $userRole = $user?->role?->posisi;
    $isAdminBarang = $userRole === 'admin_barang';
    $isAdminPengiriman = $userRole === 'admin_pengiriman';
@endphp

@if ($isAdminBarang)
    @include('layouts.partials.nav-admin-barang')
@elseif ($isAdminPengiriman)
    @include('layouts.partials.nav-admin-pengiriman')
@else
    <nav x-data="{ open: false }" class="border-b border-gray-100 bg-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-8">
                <a href="{{ route('home') }}">
                    <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                </a>
            <div class="hidden sm:flex flex-1">
                <form action="{{ route('search') }}" method="GET" class="relative w-full">
                    <input type="text" name="query"
                        class="block w-full rounded-md border border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="{{ __('Cari') }}" />

                    <button type="submit"
                        class="absolute top-0 right-0 px-3 py-1 text-gray-500 hover:text-gray-900">
                        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M9 16.17l7.5-6.5c.8-.8.8-2.1 0-2.9-.8-.8-2.1-.8-2.9 0L9 16.17zm4.97-9.09c.4-.4 1.02-.4 1.42 0l6.97 6.03c.4.4.4 1.02 0 1.42-.4.4-1.02.4-1.42 0L13.03 5.97c-.4-.4-1.02-.4-1.42 0z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                </form>
            </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:space-x-4">
                @auth
                    <a href="{{ route('cart.index') }}" class="inline-flex items-center  px-3 py-1 text-xs font-semibold uppercase tracking-wide">
                        <x-lucide-shopping-cart class="w-6 h-6 text-gray-500 hover:border-gray-300 hover:text-gray-900" />
                    </a>
                    <a href="{{ route('orders.index') }}" class="inline-flex items-center  px-3 py-1 text-xs font-semibold uppercase tracking-wide">
                        <x-lucide-package class="w-6 h-6 text-gray-500 hover:border-gray-300 hover:text-gray-900" />
                    </a>
                @endauth
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="flex items-center gap-2 rounded-md border border-transparent px-3 py-2 text-sm font-medium text-gray-600 transition hover:text-gray-900 focus:outline-none">
                                <span>{{ Auth::user()->name }}</span>
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 011.08 1.04l-4.25 4.25a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('orders.index')">
                                {{ __('Pesanan Saya') }}
                            </x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900">{{ __('Log in') }}</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-gray-700">
                            {{ __('Register') }}
                        </a>
                    @endif
                @endauth
            </div>

            <div class="flex sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center rounded-md p-2 text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 focus:outline-none">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <div :class="{ 'block': open, 'hidden': ! open }" class="sm:hidden">
            <div class="space-y-1 border-t border-gray-200 px-4 py-3">
                <x-responsive-nav-link :href="route('home')" :active="request()->routeIs('home')">
                    {{ __('Beranda') }}
                </x-responsive-nav-link>
                @auth
                    <x-responsive-nav-link :href="route('cart.index')" :active="request()->routeIs('cart.*')">
                        {{ __('Keranjang') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('orders.index')" :active="request()->routeIs('orders.*')">
                        {{ __('Pesanan') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-responsive-nav-link>
                @endauth
            </div>

            <div class="border-t border-gray-200 px-4 py-4">
                @auth
                    <div class="mb-3">
                        <p class="text-base font-semibold text-gray-900">{{ Auth::user()->name }}</p>
                        <p class="text-sm text-gray-500">{{ Auth::user()->email }}</p>
                    </div>
                    <div class="space-y-1">
                        <x-responsive-nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.edit')">
                            {{ __('Profile') }}
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('orders.index')" :active="request()->routeIs('orders.*')">
                            {{ __('Pesanan Saya') }}
                        </x-responsive-nav-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-responsive-nav-link>
                        </form>
                    </div>
                @else
                    <div class="space-y-2">
                        <x-responsive-nav-link :href="route('login')" :active="request()->routeIs('login')">
                            {{ __('Log in') }}
                        </x-responsive-nav-link>
                        @if (Route::has('register'))
                            <x-responsive-nav-link :href="route('register')" :active="request()->routeIs('register')">
                                {{ __('Register') }}
                            </x-responsive-nav-link>
                        @endif
                    </div>
                @endauth
            </div>
        </div>
    </nav>
@endif

