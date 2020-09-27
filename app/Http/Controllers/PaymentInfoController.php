<?php

namespace App\Http\Controllers;

use App\Http\Resources\PaymentInfoResource;
use App\PaymentInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentInfoController extends Controller
{
    public function save(Request $request)
    {
        $user = Auth::user();
        $isResultOk = false;
        
        $validatedData = $request->validate([
            'id' => 'nullable|numeric',
            'cardNumber' => 'required|regex:/^[0-9]{16,128}$/',
            'expirationMonth' => 'required|integer|min:1|max:12',
            'expirationYear' => 'required|integer|min:2020|max:2030',
        ]);

        $newPayment = null;
        if (isset($validatedData['id'])) { $newPayment = PaymentInfo::find($validatedData['id']); }
        else { $newPayment = new PaymentInfo(); }
        
        $newPayment->user_id = $user->id;
        $newPayment->type = PaymentInfo::getRandomType();
        $newPayment->card_number = $validatedData['cardNumber'];
        $newPayment->expiration_month = $validatedData['expirationMonth'];
        $newPayment->expiration_year = $validatedData['expirationYear'];
        $newPayment->save();



        $isResultOk = true;

        return [
            'isResultOk' => $isResultOk,
            'validatedData' => $validatedData,
            'newPayment' => new PaymentInfoResource($newPayment)
        ];
    }
}
