<?php

use App\BmdAuth;
use Illuminate\Database\Seeder;

class AddFrontendPseudoExpiresInToBmdAuths extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $auths = BmdAuth::all();

        foreach ($auths as $a) {
            $a->frontend_pseudo_expires_in = $a->expires_in - 1;
            $a->save();
        }
    }
}
