<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\BmdHelpers\BmdAuthProvider;

class BmdAuthController extends Controller
{
    public function checkBmdAuthValidity(Request $r)
    {

        $bmdAuth = BmdAuthProvider::getInstance();
        $user = BmdAuthProvider::user();

        return [
            'isResultOk' => $r->bmdToken === $bmdAuth->token ? true : false,
            'objs' => [
                'email' => $user->email,
                'bmdToken' => $bmdAuth->token,
                'bmdRefreshToken' => $bmdAuth->refresh_token,
                'frontendPseudoExpiresIn' => $bmdAuth->frontend_pseudo_expires_in,
                'expiresIn' => $bmdAuth->expires_in,
                'authProviderId' => $bmdAuth->auth_provider_type_id,
            ],
        ];
    }
}
