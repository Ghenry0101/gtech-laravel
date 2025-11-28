<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\InventoryService;
use Illuminate\View\View;

class AdminBarangController extends Controller
{
    public function __construct(
        private readonly InventoryService $inventory
    ) {
    }

    public function index(): View
    {
        // InventoryService aggregates spotlight cards (stok, promo, top selling) so the dashboard can instantly present products to the client.
        return view('admin.barang.dashboard', $this->inventory->dashboardData());
    }
}
