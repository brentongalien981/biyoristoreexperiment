<?php

namespace App\Http\Controllers;

use App\Cart;
use App\Http\Resources\CartResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function show(Request $request)
    {
        $isResultOk = false;
        $user = Auth::user();
        $cart = $user->carts()->where('is_active', 1)->take(1)->get();
        $cart = count($cart) > 0 ? new CartResource($cart[0]) : null;
        // $cart = Cart::find($cart[]);

        $isResultOk = true;

        return [
            'isResultOk' => $isResultOk,
            'message' => 'From CLASS: CartController, METHOD: show()',
            'obj' => $cart,
        ];
    }
}
