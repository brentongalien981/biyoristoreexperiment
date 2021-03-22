<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\BmdHelpers\BmdAuthProvider;
use Illuminate\Support\Facades\Cache;

class BmdAuthController extends Controller
{
    public function checkBmdAuthValidity(Request $r)
    {

        // TODO
        $bmdAuth = BmdAuthProvider::getInstance();
        $isResultOk = false;
        // $user = BmdAuthProvider::user();

        //
        $stayLoggedIn = $bmdAuth->stayLoggedIn;
        if (
            isset($stayLoggedIn)
            && $stayLoggedIn == 1
        ) {
            $isResultOk = true;

            $numOfOpenBrowserTabs = $bmdAuth->numOfOpenBrowserTabs;
            if (!isset($numOfOpenBrowserTabs) || $numOfOpenBrowserTabs < 0) { $numOfOpenBrowserTabs = 0; }
            $updatedNumOfOpenBrowserTabs = $numOfOpenBrowserTabs + 1;

            $bmdAuth->numOfOpenBrowserTabs = $updatedNumOfOpenBrowserTabs;

            // Update the bmd-auth cache-record only.
            Cache::store('redisprimary')->put($bmdAuth->getCacheKey(), $bmdAuth);
            // 1618997148


        }

        throw new Exception('muhaha');

        return [
            'isResultOk' => $isResultOk,
            'objs' => [
                // 'email' => $user->email,
                'bmdToken' => $bmdAuth->token,
                'bmdRefreshToken' => $bmdAuth->refresh_token,
                'frontendPseudoExpiresIn' => $bmdAuth->frontend_pseudo_expires_in,
                'expiresIn' => $bmdAuth->expires_in,
                'authProviderId' => $bmdAuth->auth_provider_type_id,
            ],
        ];
    }
}
