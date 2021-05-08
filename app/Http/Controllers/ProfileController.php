<?php

namespace App\Http\Controllers;

use App\Order;
use Exception;
use App\Address;
use App\Profile;
use App\StripeCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\OrderResource;
use App\Http\Resources\AddressResource;
use App\Http\Resources\ProfileResource;
use App\Http\BmdHelpers\BmdAuthProvider;
use App\Http\Resources\PaymentInfoResource;

class ProfileController extends Controller
{
    public function save(Request $request)
    {
        
        $user = BmdAuthProvider::user();
        $overallProcessLogs = ['In CLASS: ProfileController, METHOD: save()'];
        $isResultOk = false;

        // BMD-DELETE: On ITER-DEV-004: Delete.
        // $emailValidationCriteria = "email|min:8|max:64";
        // if ($user->email != $request->email) {
        //     $emailValidationCriteria .= "|unique:users";
        // }

        $validatedData = $request->validate([
            // 'email' => $emailValidationCriteria,
            'firstName' => 'nullable|alpha|max:128',
            'lastName' => 'nullable|alpha|max:128',
            'phone' => 'nullable|string|max:16'
        ]);


        $profile = $user->profile;
        $profile->first_name = isset($validatedData['firstName']) ? $validatedData['firstName'] : "";
        $profile->last_name = isset($validatedData['lastName']) ? $validatedData['lastName'] : "";
        $profile->phone = isset($validatedData['phone']) ? $validatedData['phone'] : "";
        $profile->save();
        $overallProcessLogs[] = 'updated profile saved';

        $saveData = Profile::saveProfileToCache($profile);
        $profile = $saveData['mainData'];
        $overallProcessLogs = array_merge($overallProcessLogs, $saveData['processLogs']);

        $isResultOk = true;


        return [
            'isResultOk' => $isResultOk,
            'validatedData' => $validatedData,
            'objs' => [
                'profile' => $profile ?? [],
                // 'overallProcessLogs' => $overallProcessLogs,
            ],
        ];
    }



    public function show(Request $request)
    {
        $user = BmdAuthProvider::user();
        $overallProcessLogs = [];
        $resultCode = 0;


        $readData = StripeCustomer::getPaymentMethodsFromCacheWithUser($user);
        $paymentMethods = $readData['mainData'];
        $overallProcessLogs = array_merge($overallProcessLogs, $readData['processLogs']);


        $readData = Profile::getProfileFromCacheWithUser($user);
        $profile = $readData['mainData'];
        $overallProcessLogs = array_merge($overallProcessLogs, $readData['processLogs']);


        $readData = Address::getAddressesFromCacheWithUser($user);
        $addresses = $readData['mainData'];
        $overallProcessLogs = array_merge($overallProcessLogs, $readData['processLogs']);
        
        $resultCode = 1;

        
        return [
            'objs' => [
                'profile' => $profile ?? [],
                'paymentInfos' => $paymentMethods ?? [],
                'addresses' => $addresses ?? [],
            ],
            // 'overallProcessLogs' => $overallProcessLogs,
            'resultCode' => $resultCode,
        ];
    }
}
