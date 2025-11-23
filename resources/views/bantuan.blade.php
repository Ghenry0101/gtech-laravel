<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2">
            <p class="text-xs uppercase tracking-wide text-slate-400">Bantuan</p>
            <h1 class="text-2xl font-semibold text-slate-900">Pusat Bantuan & FAQ</h1>
            <p class="text-sm text-slate-500">Panduan cepat untuk pesanan, pembayaran, dan pengiriman di GTech.</p>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto space-y-8 px-4 sm:px-6 lg:px-8">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                <h2 class="text-lg font-semibold text-slate-900">Pesanan & Pembayaran</h2>
                <div class="space-y-3 text-sm text-slate-600">
                    <p><span class="font-semibold text-slate-900">Bagaimana cara checkout?</span><br>Tambahkan produk ke keranjang, cek ringkasan, lalu lanjut ke checkout dan pilih metode pembayaran.</p>
                    <p><span class="font-semibold text-slate-900">Metode pembayaran apa saja?</span><br>Virtual Account, e-wallet, dan metode lain yang tersedia di Midtrans akan ditampilkan saat checkout.</p>
                    <p><span class="font-semibold text-slate-900">Bagaimana melihat status pembayaran?</span><br>Status pembayaran tampil di detail pesanan. Jika menunggu, selesaikan sebelum batas waktu yang tertera.</p>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                <h2 class="text-lg font-semibold text-slate-900">Pengiriman</h2>
                <div class="space-y-3 text-sm text-slate-600">
                    <p><span class="font-semibold text-slate-900">Kapan resi muncul?</span><br>Resi dan status terkini akan tampil otomatis di halaman pesanan setelah diproses kurir.</p>
                    <p><span class="font-semibold text-slate-900">Berapa estimasi tiba?</span><br>Estimasi hari sampai tertera di detail pengiriman dan mengikuti layanan kurir yang dipilih.</p>
                    <p><span class="font-semibold text-slate-900">Bagaimana melacak paket?</span><br>Buka detail pesanan dan lihat timeline tracking yang terupdate.</p>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                <h2 class="text-lg font-semibold text-slate-900">Komplain & Pengembalian</h2>
                <div class="space-y-3 text-sm text-slate-600">
                    <p><span class="font-semibold text-slate-900">Kapan bisa komplain?</span><br>Setelah pesanan diterima, ajukan komplain di halaman pesanan jika ada kerusakan, salah barang, atau kurang.</p>
                    <p><span class="font-semibold text-slate-900">Apa yang perlu disiapkan?</span><br>Sertakan foto bukti dan deskripsi singkat agar tim kami dapat memproses lebih cepat.</p>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-2">
                <h2 class="text-lg font-semibold text-slate-900">Butuh bantuan langsung?</h2>
                <p class="text-sm text-slate-600">Hubungi kami melalui email atau WhatsApp. Kami siap membantu setiap hari kerja.</p>
                <div class="flex flex-wrap gap-2 text-sm">
                    <a href="mailto:{{ config('mail.from.address', 'support@gtech.id') }}" class="rounded-lg border border-slate-200 px-3 py-2 font-semibold text-slate-800 hover:border-slate-300">{{ config('mail.from.address', 'support@gtech.id') }}</a>
                    <a href="https://wa.me/{{ preg_replace('/\D+/', '', config('app.support_whatsapp', '628123456789')) }}" target="_blank" class="rounded-lg bg-slate-900 px-3 py-2 font-semibold text-white hover:bg-slate-800">WhatsApp</a>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
