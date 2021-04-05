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



    public function read(Request $r)
    {
        $userId = null;

        if (BmdAuthProvider::check()) {
            $userId = BmdAuthProvider::user()->id;
        } else {
            $userId = $r->temporaryGuestUserId;
        }

        $resultData = Cart::getUserCartFromCache($userId);
        $cart = $resultData['mainData'];


        return [
            'isResultOk' => true,
            'objs' => [
                'cart' => $cart
            ],
        ];
    }
}
