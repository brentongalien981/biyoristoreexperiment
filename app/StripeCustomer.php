<?php

namespace App;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

class StripeCustomer extends Model
{
    public static function clearCachePaymentMethodsWithUser($user) {

        $processLogs = ['In CLASS: StripeCustomer, METHOD: clearCachePaymentMethodsWithUser()'];
        $cacheKey = 'stripePaymentMethods?userId=' . $user->id;

        Cache::store('redisprimary')->forget($cacheKey);
        $processLogs[] = 'cleared user-payment-methods in cache';


        return [
            'processLogs' => $processLogs
        ];
    }



    public static function getPaymentMethodsFromCacheWithUser($user)
    {
        $stripe = new \Stripe\StripeClient(env('STRIPE_SK'));
        $processLogs = ['In CLASS: StripeCustomer, METHOD: getPaymentMethodsFromCacheWithUser()'];

        $cacheKey = 'stripePaymentMethods?userId=' . $user->id;
        $mainData = Cache::store('redisreader')->get($cacheKey);

        if (isset($mainData)) {

            $processLogs[] = 'mainData is from cache';
        } else {

            $mainData = $stripe->paymentMethods->all([
                'customer' => $user->stripeCustomer->stripe_customer_id,
                'type' => 'card',
            ]);
            $mainData = $mainData['data'];

            $processLogs[] = 'has just read mainData from stripe.com';


            Cache::store('redisprimary')->put($cacheKey, $mainData, now()->addDays(7));
            $processLogs[] = 'has just saved mainData to cache';
        }


        return [
            'mainData' => $mainData,
            'processLogs' => $processLogs
        ];
    }
}
