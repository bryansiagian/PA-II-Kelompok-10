<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\Request;

class CartApiController extends Controller
{
    public function index()
    {
        return Cart::with('product.category')
            ->where('user_id', auth()->id())
            ->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        $cart = Cart::where('user_id', auth()->id())
            ->where('product_id', $request->product_id)
            ->first();

        if ($cart) {
            $cart->increment('quantity');
        } else {
            Cart::create([
                'user_id' => auth()->id(),
                'product_id' => $request->product_id,
                'quantity' => 1
            ]);
        }

        return response()->json(['message' => 'Berhasil ditambah ke keranjang']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = Cart::with('product')
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        if ($request->quantity > $cart->product->stock) {
            return response()->json([
                'message' => 'Stok hanya tersedia ' . $cart->product->stock,
                'max_stock' => $cart->product->stock
            ], 422);
        }

        $cart->update([
            'quantity' => $request->quantity
        ]);

        return response()->json(['message' => 'Kuantitas diperbarui']);
    }

    public function destroy($id)
    {
        Cart::where('user_id', auth()->id())
            ->findOrFail($id)
            ->delete();

        return response()->json(['message' => 'Item dihapus']);
    }

    public function clear()
    {
        Cart::where('user_id', auth()->id())->delete();

        return response()->json(['message' => 'Keranjang dikosongkan']);
    }
}
