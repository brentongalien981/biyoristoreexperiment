<?php

namespace App\Http\Controllers;

use Exception;
use App\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\AddressResource;
use App\Http\BmdHelpers\BmdAuthProvider;

class AddressController extends Controller
{
    public function destroy(Request $request)
    {        
        $validatedData = $request->validate([
            'addressId' => 'required|numeric',
        ]);


        $isResultOk = false;
        $overallProcessLogs = ['In CLASS: AddressController, METHOD: save()'];
        
        $user = BmdAuthProvider::user();
        $a = Address::find($validatedData['addressId']);


        if (Gate::forUser($user)->authorize('delete', $a)) {

            Address::destroy($validatedData['addressId']);
            $overallProcessLogs[] = 'deleted address from db';

            $resultData = Address::clearCacheAddressesWithUserId($user->id);
            $overallProcessLogs = array_merge($overallProcessLogs, $resultData['processLogs']);

            $isResultOk = true;
        }




        return [
            'isResultOk' => $isResultOk,
            // 'validatedData' => $validatedData,
            // 'overallProcessLogs' => $overallProcessLogs
        ];
    }

    public function save(Request $request)
    {               
        
        $validatedData = $request->validate([
            'id' => 'nullable|numeric',
            'street' => 'required|string|max:128',
            'city' => 'required|string|max:64',
            'province' => 'required|string|max:32',
            'country' => 'required|string|max:32',
            'postalCode' => 'required|string|max:16',
        ]);


        $user = BmdAuthProvider::user();
        $overallProcessLogs = ['In CLASS: AddressController, METHOD: save()'];

        $address = null;
        if (isset($validatedData['id'])) { 
            $address = Address::find($validatedData['id']); 

            if (Gate::forUser($user)->authorize('update', $address)) {}
        }
        else { $address = new Address(); }
        
        $address->user_id = $user->id;
        $address->street = $validatedData['street'];
        $address->city = $validatedData['city'];
        $address->province = $validatedData['province'];
        $address->country = $validatedData['country'];
        $address->postal_code = $validatedData['postalCode'];
        $address->save();
        $overallProcessLogs[] = 'saved mainData to db';


        $resultData = Address::clearCacheAddressesWithUserId($address->user_id);
        $overallProcessLogs = array_merge($overallProcessLogs, $resultData['processLogs']);


        return [
            'isResultOk' => true,
            // 'overallProcessLogs' => $overallProcessLogs
        ];
    }
}
