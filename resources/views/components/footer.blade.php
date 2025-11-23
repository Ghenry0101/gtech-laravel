<footer class="border-t border-slate-800 bg-slate-950 text-slate-100">
    <div class="max-w-7xl mx-auto px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
            <div class="space-y-3">
                <p class="text-xs uppercase tracking-wide text-slate-400">Tentang GTech</p>
                <p class="text-sm leading-relaxed text-slate-200">
                    GTech menghadirkan pengalaman belanja teknologi yang kurasi, dengan pengiriman cepat dan dukungan pelanggan yang sigap.
                </p>
                <p class="text-sm text-slate-400">
                    Dibangun untuk memudahkan Anda menemukan perangkat terbaik tanpa ribet.
                </p>
            </div>

            <div class="space-y-3">
                <p class="text-xs uppercase tracking-wide text-slate-400">Bantuan</p>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ url('/bantuan') }}" class="hover:text-white">FAQ & Pusat Bantuan</a></li>
                    <li><a href="mailto:{{ config('mail.from.address', 'support@gtech.id') }}" class="hover:text-white">Hubungi Kami</a></li>
                </ul>
            </div>

            <div class="space-y-3">
                <p class="text-xs uppercase tracking-wide text-slate-400">Kontak</p>
                <div class="space-y-2 text-sm">
                    <p>Email: <a href="mailto:{{ config('mail.from.address', 'support@gtech.id') }}" class="font-semibold text-white hover:text-slate-200">{{ config('mail.from.address', 'support@gtech.id') }}</a></p>
                    <p>Telepon: <span class="font-semibold text-white">{{ config('app.support_phone', '0812-3456-7890') }}</span></p>
                </div>
                <div class="space-y-2">
                    <p class="text-xs uppercase tracking-wide text-slate-400">Sosial</p>
                    <div class="flex items-center gap-2">
                        @php
                            $socialLinks = [
                                ['label' => 'IG', 'url' => config('app.social_instagram', 'https://instagram.com')],
                                ['label' => 'FB', 'url' => '#'],
                                ['label' => 'X', 'url' => '#'],
                            ];
                        @endphp
                        @foreach ($socialLinks as $social)
                            <a href="{{ $social['url'] }}" target="_blank" rel="noreferrer" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-700 text-xs font-semibold text-slate-200 hover:border-slate-500 hover:text-white">
                                {{ $social['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <p class="text-xs uppercase tracking-wide text-slate-400">Kebijakan</p>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ url('/kebijakan') }}" class="hover:text-white">Syarat & Ketentuan</a></li>
                    <li><a href="{{ url('/kebijakan') }}" class="hover:text-white">Kebijakan Privasi</a></li>
                </ul>
            </div>
        </div>

        <div class="mt-10 flex flex-col gap-2 border-t border-slate-800 pt-6 text-sm text-slate-400 sm:flex-row sm:items-center sm:justify-between">
            <p>© {{ date('Y') }} {{ config('app.name', 'GTech') }}. Semua hak dilindungi.</p>
        </div>
    </div>
</footer>
