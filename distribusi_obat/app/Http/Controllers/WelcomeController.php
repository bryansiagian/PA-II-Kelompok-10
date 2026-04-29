<?php

namespace App\Http\Controllers;

use App\Models\Product;

class WelcomeController extends Controller
{
    public function index()
    {
        $products = Product::where('active', 1)
            ->latest()
            ->get();

        return view('welcome', compact('products'));
    }
}
