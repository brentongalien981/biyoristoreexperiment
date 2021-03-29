<?php

namespace App\Policies;

use App\Address;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AddressPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }



    public function update(User $u, Address $a)
    {
        return $u->id === $a->user_id;
    }



    public function delete(User $u, Address $a)
    {
        return $u->id === $a->user_id;
    }
}
