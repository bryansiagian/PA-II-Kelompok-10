<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;

class ProductOrderController extends Controller
{
    // 🔥 AMBIL RIWAYAT PESANAN USER
    public function myOrders()
{
    $orders = \App\Models\Order::with(['items.product'])
        ->where('user_id', auth()->id())
        ->latest()
        ->get();

    return response()->json($orders);
}
}
