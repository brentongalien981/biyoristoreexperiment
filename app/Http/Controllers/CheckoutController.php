<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\AddressResource;

class CheckoutController extends Controller
{
    public function readCheckoutRequiredData(Request $request)
    {
        $user = Auth::user();

        return [
            'message' => 'From CLASS: CheckoutController, METHOD: readCheckoutRequiredData()',
            'objs' => [
                'addresses' => AddressResource::collection($user->addresses)    
            ]
        ];
    }
}
