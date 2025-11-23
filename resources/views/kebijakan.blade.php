<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2">
            <p class="text-xs uppercase tracking-wide text-slate-400">Kebijakan</p>
            <h1 class="text-2xl font-semibold text-slate-900">Kebijakan & Pengembalian</h1>
            <p class="text-sm text-slate-500">Ringkasan kebijakan layanan, pengiriman, dan pengembalian di GTech.</p>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto space-y-8 px-4 sm:px-6 lg:px-8">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-3">
                <h2 class="text-lg font-semibold text-slate-900">Pengiriman</h2>
                <p class="text-sm text-slate-600">Pesanan diproses setelah pembayaran terkonfirmasi. Nomor resi dan estimasi tiba ditampilkan di halaman pesanan Anda.</p>
                <ul class="list-disc list-inside text-sm text-slate-600 space-y-1">
                    <li>Estimasi tiba mengikuti pilihan layanan kurir yang tersedia.</li>
                    <li>Update status pengiriman bisa dipantau secara real-time di detail pesanan.</li>
                </ul>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-3">
                <h2 class="text-lg font-semibold text-slate-900">Pengembalian & Refund</h2>
                <p class="text-sm text-slate-600">Anda dapat mengajukan komplain bila barang salah, rusak, atau kurang.</p>
                <ul class="list-disc list-inside text-sm text-slate-600 space-y-1">
                    <li>Ajukan komplain melalui halaman pesanan setelah barang diterima.</li>
                    <li>Sertakan foto dan deskripsi masalah untuk mempercepat penanganan.</li>
                    <li>Refund dilakukan ke metode pembayaran awal setelah komplain disetujui.</li>
                </ul>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-3">
                <h2 class="text-lg font-semibold text-slate-900">Kebijakan Produk</h2>
                <p class="text-sm text-slate-600">Setiap produk menampilkan harga, stok, dan detail berat untuk perhitungan ongkir yang transparan.</p>
                <p class="text-sm text-slate-600">Diskon aktif dan batas waktunya tercantum pada detail produk dan keranjang.</p>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-3">
                <h2 class="text-lg font-semibold text-slate-900">Privasi & Keamanan</h2>
                <p class="text-sm text-slate-600">Data pribadi digunakan untuk pemrosesan pesanan, pembayaran, dan pengiriman. Kami tidak membagikan data tanpa izin.</p>
            </section>
        </div>
    </div>
</x-app-layout>
