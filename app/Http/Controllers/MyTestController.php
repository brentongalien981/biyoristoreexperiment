<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MyTestController extends Controller
{
    public function yo() {

        return [
            'msg' => 'METHOD: yo()',
            'sing' => 'te ni shita chizuwa furukunaateiku bakari'
        ];
    }



    public function flushCache() {

        Cache::flush();
        
        return [
            'msg' => 'METHOD: flushCache()'
        ];
    }
}
