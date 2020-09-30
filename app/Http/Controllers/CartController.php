<?php

namespace App\Http\Controllers;

use App\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function show(Request $request)
    {
        $isResultOk = false;
        $user = Auth::user();
        $cart = $user->carts()->where('is_active', 1)->take(1)->get()[0];
        // $cart = Cart::find($cart[]);

        $isResultOk = true;

        return [
            'isResultOk' => $isResultOk,
            'message' => 'From CLASS: CartController, METHOD: show()',
            'obj' => $cart,
            'items' => $cart->cartItems
        ];
    }
}
