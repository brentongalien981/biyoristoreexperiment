<?php

namespace App\Http\Controllers;

use App\Address;
use App\Http\Resources\AddressResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    public function destroy(Request $request)
    {
        $isResultOk = false;
        
        $validatedData = $request->validate([
            'addressId' => 'required|numeric',
        ]);

        Address::destroy($validatedData['addressId']);

        $isResultOk = true;

        return [
            'isResultOk' => $isResultOk,
            'validatedData' => $validatedData
        ];
    }

    public function save(Request $request)
    {
        $user = Auth::user();
        $isResultOk = false;
        
        $validatedData = $request->validate([
            'id' => 'nullable|numeric',
            'street' => 'required|string|max:128',
            'city' => 'required|string|max:64',
            'province' => 'required|string|max:32',
            'country' => 'required|string|max:32',
            'postalCode' => 'required|string|max:16',
        ]);

        $address = null;
        if (isset($validatedData['id'])) { $address = Address::find($validatedData['id']); }
        else { $address = new Address(); }
        
        $address->user_id = $user->id;
        $address->street = $validatedData['street'];
        $address->city = $validatedData['city'];
        $address->province = $validatedData['province'];
        $address->country = $validatedData['country'];
        $address->postal_code = $validatedData['postalCode'];
        $address->save();



        $isResultOk = true;

        return [
            'isResultOk' => $isResultOk,
            'validatedData' => $validatedData,
            'address' => new AddressResource($address)
        ];
    }
}
