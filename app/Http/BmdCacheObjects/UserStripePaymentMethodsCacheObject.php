<?php

namespace App\Http\BmdCacheObjects;

use App\Http\BmdHelpers\BmdAuthProvider;

class UserStripePaymentMethodsCacheObject extends BmdModelCacheObject
{
    protected $lifespanInMin = 1440;


    /**
     * @override
     * @return UserStripePaymentMethodsCacheObject
     */
    public function getMyRefreshedVersion()
    {

        if (!$this->shouldRefresh()) { return $this; }

        // BMD-ON-STAGING: Change this to STRIPE_PK.
        $stripe = new \Stripe\StripeClient(env('STRIPE_SK'));
        $u = BmdAuthProvider::user();

        $paymentMethods = $stripe->paymentMethods->all([
            'customer' => $u->stripeCustomer->stripe_customer_id,
            'type' => 'card',
        ]);

        $this->data = $paymentMethods['data'];
        $this->save();
        return $this;
    }
}
