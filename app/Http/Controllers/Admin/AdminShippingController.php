<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ShipmentDetailRequest;
use App\Http\Requests\Admin\ShipmentStatusRequest;
use App\Models\Order;
use App\Services\Admin\AdminShippingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminShippingController extends Controller
{
    public function __construct(
        private readonly AdminShippingService $shipping
    ) {
    }

    /**
     * Halaman utama admin pengiriman: statistik, antrian, dan pencarian pesanan.
     */
    public function index(Request $request): View
    {
        $data = $this->shipping->dashboardData(
            (string) $request->query('status', 'processing'),
            (string) $request->query('q', '')
        );

        return view('admin.pengiriman.dashboard', $data);
    }

    /**
     * Detail pesanan + pengiriman untuk admin pengiriman.
     */
    public function show(Order $order): View
    {
        abort_unless($this->shipping->hasShipment($order), 404);

        return view('admin.pengiriman.orders.show', [
            'order' => $this->shipping->loadOrderDetails($order),
        ]);
    }

    /**
     * Perbarui metadata pengiriman (kurir, layanan, resi, estimasi).
     */
    public function updateShipment(ShipmentDetailRequest $request, Order $order): RedirectResponse
    {
        abort(403, 'Admin pengiriman tidak dapat mengubah estimasi pengiriman.');
    }

    /**
     * Perbarui status pengiriman dan sinkronkan dengan status pesanan pelanggan.
     */
    public function updateStatus(ShipmentStatusRequest $request, Order $order): RedirectResponse
    {
        abort(403, 'Admin pengiriman tidak dapat mengubah status pengiriman.');
    }
}
