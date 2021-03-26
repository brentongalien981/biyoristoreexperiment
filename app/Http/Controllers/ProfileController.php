<?php

namespace App\Http\Controllers;

use App\Http\BmdHelpers\BmdAuthProvider;
use App\Http\Resources\AddressResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\PaymentInfoResource;
use App\Http\Resources\ProfileResource;
use App\Order;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function save(Request $request)
    {
        
        $user = Auth::user();

        // TODO: On ITER-DEV-005: Delete.
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

        
        // $user->email = $validatedData['email'];
        // $user->save();

        $profile = $user->profile;
        $profile->first_name = isset($validatedData['firstName']) ? $validatedData['firstName'] : "";
        $profile->last_name = isset($validatedData['lastName']) ? $validatedData['lastName'] : "";
        $profile->phone = isset($validatedData['phone']) ? $validatedData['phone'] : "";
        $profile->save();


        //
        return [
            'validatedData' => $validatedData,
            'profile' => new ProfileResource($profile),
            'message' => "In CLASS: ProfileController, METHOD: save()"
        ];
    }



    public function show(Request $request)
    {
        $user = BmdAuthProvider::user();

        //
        $stripe = new \Stripe\StripeClient(env('STRIPE_SK'));

        $paymentMethods = $stripe->paymentMethods->all([
            'customer' => $user->stripeCustomer->stripe_customer_id,
            'type' => 'card',
        ]);


        
        return [
            'objs' => [
                'profile' => new ProfileResource($user->profile),
                'paymentInfos' => $paymentMethods['data'],
                'addresses' => AddressResource::collection($user->addresses),
            ],
        ];
    }
}
