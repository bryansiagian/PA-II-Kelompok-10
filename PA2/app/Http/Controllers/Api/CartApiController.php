<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;

class CartApiController extends Controller
{
    public function index()
    {
        $carts = Cart::with('product')
            ->where('user_id', Auth::id())
            ->get();

        return response()->json($carts);
    }

    public function store(Request $request)
    {
        $cart = Cart::where('user_id', Auth::id())
            ->where('product_id', $request->product_id)
            ->first();

        if ($cart) {
            $cart->increment('qty');
        } else {
            Cart::create([
                'user_id' => Auth::id(),
                'product_id' => $request->product_id,
                'qty' => 1
            ]);
        }

        return response()->json([
            'message' => 'Produk masuk keranjang'
        ]);
    }

    public function destroy($id)
    {
        Cart::where('id', $id)
            ->where('user_id', Auth::id())
            ->delete();

        return response()->json(['message' => 'hapus']);
    }

    public function checkout()
{
    $userId = auth()->id();

    $carts = Cart::with('product')
        ->where('user_id', $userId)
        ->get();

    if ($carts->isEmpty()) {
        return response()->json(['message' => 'Cart kosong'], 400);
    }

    $total = 0;

    foreach ($carts as $cart) {
        $total += $cart->product->price * $cart->qty;
    }

    $order = Order::create([
        'user_id' => $userId,
        'total' => $total,
        'status' => 'pending'
    ]);

    foreach ($carts as $cart) {
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $cart->product_id,
            'qty' => $cart->qty,
            'price' => $cart->product->price
        ]);
    }

    // Hapus semua cart setelah checkout
    Cart::where('user_id', $userId)->delete();

    return response()->json([
        'message' => 'Checkout berhasil',
        'order_id' => $order->id
    ]);
}
}
