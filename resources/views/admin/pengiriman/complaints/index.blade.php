@php
    $statusBadges = [
        'pending' => 'bg-amber-100 text-amber-800',
        'resolved' => 'bg-emerald-100 text-emerald-800',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-400">{{ __('Admin Pengiriman') }}</p>
                <h1 class="text-2xl font-semibold text-slate-900">{{ __('Komplain Pelanggan') }}</h1>
            </div>
            <a href="{{ route('admin.shipping.dashboard') }}" class="inline-flex items-center justify-center rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:border-slate-300 hover:text-slate-900">
                {{ __('Kembali ke Dasbor') }}
            </a>
        </div>
    </x-slot>

    <div class="py-10 space-y-6 p-6">
        @if (session('status_message'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-6 py-4 text-sm text-emerald-800">
                {{ session('status_message') }}
            </div>
        @endif

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold text-slate-900">{{ __('Daftar komplain terbaru') }}</p>
            <p class="text-xs text-slate-500">{{ __('Gunakan tombol di kanan untuk menandai selesai.') }}</p>

            <div class="mt-5 overflow-hidden rounded-2xl border border-slate-100">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-6 py-3">{{ __('Detail Komplain') }}</th>
                            <th class="px-6 py-3">{{ __('Pesanan & Pelanggan') }}</th>
                            <th class="px-6 py-3">{{ __('Status') }}</th>
                            <th class="px-6 py-3 text-right">{{ __('Update') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white text-sm text-slate-700">
                        @forelse ($complaints as $complaint)
                            @php
                                $badge = $statusBadges[$complaint->status] ?? 'bg-slate-100 text-slate-700';
                            @endphp
                            <tr>
                                <td class="px-6 py-4 align-top">
                                    <p class="font-semibold text-slate-900">{{ $complaint->reason }}</p>
                                    <p class="text-xs text-slate-500">{{ optional($complaint->created_at)->format('d M Y H:i') }}</p>
                                    <p class="mt-3 whitespace-pre-line text-sm text-slate-600">{{ $complaint->issue_detail }}</p>
                                </td>
                                <td class="px-6 py-4 align-top">
                                    <p class="font-semibold text-slate-900">{{ $complaint->order->order_number ?? '-' }}</p>
                                    <p class="text-xs text-slate-500">
                                        {{ $complaint->user->name ?? '-' }}
                                        @if ($complaint->user?->phone)
                                            &middot; {{ $complaint->user?->phone }}
                                        @elseif ($complaint->user?->email)
                                            &middot; {{ $complaint->user?->email }}
                                        @endif
                                    </p>
                                </td>
                                <td class="px-6 py-4 align-top">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $badge }}">
                                        {{ $complaint->status === 'resolved' ? __('Selesai') : __('Menunggu') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <form method="POST" action="{{ route('admin.shipping.complaints.update', $complaint) }}" class="inline-flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-end">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" class="rounded-full border border-slate-200 px-3 py-1 text-xs focus:border-slate-400 focus:outline-none focus:ring-0">
                                            @foreach (\App\Models\Complaint::STATUSES as $statusOption)
                                                <option value="{{ $statusOption }}" @selected($complaint->status === $statusOption)>
                                                    {{ $statusOption === 'resolved' ? __('Selesai') : __('Menunggu') }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="inline-flex items-center justify-center rounded-full bg-slate-900 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-800">
                                            {{ __('Simpan') }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-sm text-slate-500">
                                    {{ __('Belum ada komplain.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $complaints->links() }}
            </div>
        </section>
    </div>
</x-app-layout>
