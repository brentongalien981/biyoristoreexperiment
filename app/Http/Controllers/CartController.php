<?php

namespace App\Http\Controllers;

use App\Cart;
use App\Http\BmdHelpers\BmdAuthProvider;
use App\Http\Resources\CartResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function updateUserCartCache(Request $r) {
        $u = BmdAuthProvider::user();
        // bmd-todo
    }



    public function read(Request $request)
    {
        $user = BmdAuthProvider::user();

        $resultData = Cart::getUserCartFromCache($user);
        $cart = $resultData['mainData'];


        return [
            'isResultOk' => true,
            'retrievedDataFrom' => $resultData['retrievedDataFrom'],
            'objs' => [
                'cart' => $cart
            ],
        ];
    }
}
