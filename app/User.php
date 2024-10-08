<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];



    public static function doesExistWithEmail($email) {
        $possibleUsers = self::where('email', $email)->get();

        if (isset($possibleUsers) && count($possibleUsers) === 1 && isset($possibleUsers[0])) {
            return true;
        }

        return false;
    }



    public function orders()
    {
        return $this->hasMany('App\Order');
    }



    public function stripeCustomer()
    {
        return $this->hasOne('App\StripeCustomer');
    }



    public function getActiveCart()
    {
        $cart = $this->carts()->where('is_active', 1)->take(1)->get();

        if (isset($cart) && count($cart) > 0) { return $cart[0]; }

        $cart = new Cart();
        $cart->user_id = $this->id;
        $cart->is_active = 1;
        $cart->save();

        return $cart;
    }



    public function carts()
    {
        return $this->hasMany('App\Cart');
    }



    public function addresses()
    {
        return $this->hasMany('App\Address');
    }



    public function paymentInfos()
    {
        return $this->hasMany('App\PaymentInfo');
    }



    public function profile() {
        return $this->hasOne('App\Profile');
    }
}
