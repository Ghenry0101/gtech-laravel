@php
    use Illuminate\Support\Facades\Route;

    $user = Auth::user();
    $userRole = $user?->role?->posisi;
    $isAdminBarang = $userRole === 'admin_barang';
@endphp

@if ($isAdminBarang)
    <nav x-data="{ open: false }" class="border-b border-slate-200 bg-white/90 backdrop-blur">
        @php
            $primaryDashboardRoute = 'admin.barang.dashboard';
            $navLinks = [
                [
                    'title' => __('Dashboard'),
                    'route' => 'admin.barang.dashboard',
                    'active' => ['admin.barang.dashboard'],
                    'show' => true,
                ],
                [
                    'title' => __('Kelola Produk'),
                    'route' => 'admin.barang.products.index',
                    'active' => ['admin.barang.products.index', 'admin.barang.products.create', 'admin.barang.products.edit'],
                    'show' => Route::has('admin.barang.products.index'),
                ],
                [
                    'title' => __('Kategori Produk'),
                    'route' => 'admin.barang.categories.index',
                    'active' => ['admin.barang.categories.index', 'admin.barang.categories.create', 'admin.barang.categories.edit'],
                    'show' => Route::has('admin.barang.categories.index'),
                ],
            ];
        @endphp

        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-8">
                <a href="{{ route($primaryDashboardRoute) }}" class="flex items-center gap-2 rounded-xl bg-slate-900 px-3 py-1 text-sm font-semibold uppercase tracking-wide text-white shadow-sm shadow-slate-900/30 hover:bg-slate-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-900">
                    <x-application-logo class="h-6 w-auto fill-current" />
                    <span>GTech Admin</span>
                </a>

                <div class="hidden items-center gap-1 text-sm sm:flex">
                    @foreach ($navLinks as $link)
                        @continue (! $link['show'])
                        @php($activePatterns = $link['active'] ?? [$link['route']])
                        <x-nav-link :href="route($link['route'])" :active="request()->routeIs(...$activePatterns)">
                            {{ $link['title'] }}
                        </x-nav-link>
                    @endforeach
                </div>
            </div>

            <div class="hidden items-center gap-4 sm:flex">
                <div class="text-right">
                    <p class="text-sm font-semibold text-slate-900">{{ $user->name }}</p>
                    <p class="text-xs text-slate-500">{{ $user?->role?->display_name ?? \Illuminate\Support\Str::headline((string) $userRole) }}</p>
                </div>
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center gap-2 rounded-full border border-slate-300 bg-white/70 px-3 py-1 text-sm font-medium text-slate-600 shadow-sm transition hover:border-slate-400 hover:text-slate-900 focus:outline-none focus-visible:ring focus-visible:ring-slate-500/30">
                            <span>{{ __('Menu') }}</span>
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 011.08 1.04l-4.25 4.25a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>
                        <x-dropdown-link :href="route($primaryDashboardRoute)">
                            {{ __('Dashboard') }}
                        </x-dropdown-link>

                        <div class="border-t border-slate-100 my-1"></div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center rounded-md p-2 text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus-visible:ring focus-visible:ring-slate-500/30">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <div :class="{ 'block': open, 'hidden': ! open }" class="sm:hidden">
            <div class="space-y-1 border-t border-slate-200 bg-white px-4 py-3">
                @foreach ($navLinks as $link)
                    @continue (! $link['show'])
                    @php($activePatterns = $link['active'] ?? [$link['route']])
                    <x-responsive-nav-link :href="route($link['route'])" :active="request()->routeIs(...$activePatterns)">
                        {{ $link['title'] }}
                    </x-responsive-nav-link>
                @endforeach
            </div>

            <div class="border-t border-slate-200 bg-white px-4 py-4">
                <div class="mb-3">
                    <p class="text-base font-semibold text-slate-900">{{ $user->name }}</p>
                    <p class="text-sm text-slate-500">{{ $user->email }}</p>
                </div>
                <div class="space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.edit')">
                        {{ __('Profile') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route($primaryDashboardRoute)" :active="request()->routeIs($primaryDashboardRoute)">
                        {{ __('Dashboard') }}
                    </x-responsive-nav-link>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>
        </div>
    </nav>
@else
    <nav x-data="{ open: false }" class="border-b border-gray-100 bg-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-8">
                <a href="{{ route('home') }}">
                    <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                </a>
                <div class="hidden space-x-8 sm:flex">
                    <x-nav-link :href="route('home')" :active="request()->routeIs('home')">
                        {{ __('Beranda') }}
                    </x-nav-link>
                    @auth
                        <x-nav-link :href="route('cart.index')" :active="request()->routeIs('cart.*')">
                            {{ __('Keranjang') }}
                        </x-nav-link>
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                    @endauth
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:space-x-4">
                @auth
                    <a href="{{ route('cart.index') }}" class="inline-flex items-center rounded-full border border-gray-200 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-gray-600 hover:border-gray-300 hover:text-gray-900">
                        {{ __('Keranjang') }}
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
