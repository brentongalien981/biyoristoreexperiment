<?php

namespace App\Http\Controllers;

use App\Cart;
use App\CartItem;
use App\Http\Requests\UpdateCartItem;
use App\Http\Resources\CartResource;
use App\Rules\NonZeroCartItemQuantity;
use App\Rules\WithinStockLimit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartItemController extends Controller
{
    public function save(Request $request)
    {
        $validatedData = $request->validate([
            'productId' => 'required|numeric',
        ]);


        $isResultOk = false;
        $user = Auth::user();
        $cart = $user->getActiveCart();

        $cart->addItemWithProductId($validatedData['productId']);
        $cart = Cart::find($cart->id);

        $isResultOk = true;

        return [
            'isResultOk' => $isResultOk,
            'message' => 'From CLASS: CartItemController, METHOD: save()',
            'productId' => $request->productId,
            'obj' => new CartResource($cart)
        ];
    }
}
