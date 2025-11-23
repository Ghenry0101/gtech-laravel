<?php

use App\Models\Order;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    if (! Schema::hasTable('orders')) {
        return;
    }

    $expiredAt = now()->subDay();

    Order::query()
        ->with(['items.product'])
        ->where('order_status', 'pending')
        ->whereNull('paid_at')
        ->where(function ($query) use ($expiredAt) {
            $query->where('order_time', '<=', $expiredAt)
                ->orWhere(function ($innerQuery) use ($expiredAt) {
                    $innerQuery->whereNull('order_time')->where('created_at', '<=', $expiredAt);
                });
        })
        ->chunkById(50, function ($orders): void {
            foreach ($orders as $order) {
                DB::transaction(function () use ($order): void {
                    foreach ($order->items as $item) {
                        if ($item->product) {
                            $item->product()->increment('stock', $item->quantity);
                        }
                    }

                    $order->delete();
                });
            }
        });
})->hourly()->name('orders:cleanup-pending')->description('Hapus pesanan pending lebih dari 24 jam yang belum dibayar')->withoutOverlapping();
