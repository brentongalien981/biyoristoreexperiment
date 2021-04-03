<?php

namespace App\Http\Controllers;

use App\Cart;
use App\Http\BmdHelpers\BmdAuthProvider;
use App\Http\Resources\CartResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function read(Request $request)
    {
        $user = BmdAuthProvider::user();
        $overallProcessLogs[] = 'In CLASS: CartController, METHOD: read().';

        
        // bmd-todo
        $resultData = Cart::getUserCartFromCache($user);
        $cart = $resultData['mainData'];
        $overallProcessLogs = array_merge($overallProcessLogs, $resultData['processLogs']);


        return [
            'isResultOk' => true,
            'overallProcessLogs' => $overallProcessLogs,
            'retrievedDataFrom' => $resultData['retrievedDataFrom'],
            'objs' => [
                'cart' => $cart
            ],
        ];
    }
}
