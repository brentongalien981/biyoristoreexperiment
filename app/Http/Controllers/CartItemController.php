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
    public function update(Request $request)
    {
        //
        $isResultOk = false;

        $validatedData = $request->validate([
            'cartItemId' => 'required|integer',
        ]);


        $cartItem = CartItem::find($validatedData['cartItemId']);

        $validatedData = $request->validate([
            'quantity' => ['required', 'integer', new WithinStockLimit($cartItem->product), new NonZeroCartItemQuantity()],
        ]);


        $cartItem->quantity = $validatedData['quantity'];
        $cartItem->save();
        $isResultOk = true;



        return [
            'isResultOk' => $isResultOk,
            'message' => 'From CLASS: CartItemController, METHOD: update()',
            'validatedData' => $validatedData
        ];
    }



    public function destroy(Request $request)
    {
        $isResultOk = false;

        $validatedData = $request->validate([
            'cartItemId' => 'required|numeric',
        ]);

        CartItem::destroy($validatedData['cartItemId']);


        $isResultOk = true;

        return [
            'isResultOk' => $isResultOk,
            'message' => 'From CLASS: CartItemController, METHOD: destroy()',
            'validatedData' => $validatedData
        ];
    }



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
