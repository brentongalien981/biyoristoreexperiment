<?php

namespace App\Http\Controllers;

use App\BmdAuth;
use Exception;
use Illuminate\Http\Request;
use App\Http\BmdHelpers\BmdAuthProvider;
use Illuminate\Support\Facades\Cache;

class BmdAuthController extends Controller
{
    public function trySalvageToken(Request $r)
    {
        $bmdAuth = BmdAuthProvider::getInstance();

        if (
            !$bmdAuth->stayLoggedIn
            && $bmdAuth->flag == BmdAuth::PSEUDO_SESSION_STATUS_FLAGGED_EXPIRING
        ) {
            $bmdAuth->frontend_pseudo_expires_in = $bmdAuth->expires_in;
            $bmdAuth->flag = BmdAuth::PSEUDO_SESSION_STATUS_IDLE;
            Cache::store('redisprimary')->put($bmdAuth->getCacheKey(), $bmdAuth, now()->addDays(30));
        }

        return [
            'isResultOk' => true,
        ];
    }



    public function flagAsExpiring(Request $r)
    {
        $bmdAuth = BmdAuthProvider::getInstance();

        if (
            !$bmdAuth->stayLoggedIn
            && $bmdAuth->flag != BmdAuth::PSEUDO_SESSION_STATUS_FLAGGED_EXPIRING
        ) {
            $bmdAuth->frontend_pseudo_expires_in = BmdAuth::getGracePeriodExpiryInSec();
            $bmdAuth->flag = BmdAuth::PSEUDO_SESSION_STATUS_FLAGGED_EXPIRING;
            Cache::store('redisprimary')->put($bmdAuth->getCacheKey(), $bmdAuth, now()->addDays(30));
        }

        return;
    }




    public function checkBmdAuthValidity(Request $r)
    {
        return [
            'isResultOk' => true,
        ];
    }
}
