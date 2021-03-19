<?php

namespace App\Http\Controllers;

use App\BmdHelpers\BmdAuthProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MyTestController extends Controller
{
    public function testbmdauth(Request $r) {
        $bmdAuth = BmdAuthProvider::getInstance();

        return [
            'msg' => 'In CLASS: MyTestController, METHOD: testbmdauth()',
            '$r->bmdToken' => $r->bmdToken,
            '$r->authProviderId' => $r->authProviderId,
            'bmdAuth' => $bmdAuth,
        ];
    }



    public function getHttpInfo(Request $r) {
        $theHeaders = getallheaders();

        return [
            'msg' => 'In CLASS: TestController, METHOD: getHttpInfo()',
            'SERVER[HTTP_HOST]' => $_SERVER['HTTP_HOST'],
            'SERVER[REMOTE_ADDR]' => $_SERVER['REMOTE_ADDR'],
            'SERVER[SERVER_NAME]' => $_SERVER['SERVER_NAME'],
            'SERVER[SERVER_ADDR]' => $_SERVER['SERVER_ADDR'],
            'X-Forwarded-For (ISP)' => $theHeaders['X-Forwarded-For'] ?? null,
            'User-Agent (BROWSER)' => $theHeaders['User-Agent'] ?? null,
            'Host (CLOSEST-NODE-TO-SERVER)' => $theHeaders['Host'] ?? null,
            'Origin (HOST-OF-FRONTEND)' => $theHeaders['Origin'] ?? null,
            'Referer (HOST-OF-FRONTEND?)' => $theHeaders['Referer'] ?? null,
        ];

    }



    
    public function yo() {

        return [
            'msg' => 'METHOD: yo()',
            'sing' => 'te ni shita chizuwa furukunaateiku bakari'
        ];
    }



    public function flushCache() {

        Cache::store('redisreader')->flush();
        Cache::store('redisprimary')->flush();
        
        return [
            'msg' => 'METHOD: flushCache()'
        ];
    }
}
