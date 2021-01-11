<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MyCacheController extends Controller
{
    public function get($key) {

        $value = Cache::get($key);
        
        return $value;

    }



    public function has(Request $request) {

        $has = Cache::has($request->key);
        
        return [
            'comment' => 'METHOD: has()',
            'request->key' => $request->key,
            'has' => $has
        ];

    }



    public function testGet(Request $request) {

        $value = Cache::get($request->key);
        
        return [
            'comment' => 'METHOD: testGet()',
            'request->key' => $request->key,
            'value' => $value
        ];

    }



    public function testPut(Request $request) {

        Cache::put($request->key, $request->value);
        
        return [
            'comment' => 'METHOD: testPut()',
            'request->key' => $request->key,
            'request->value' => $request->value
        ];

    }

    public static function put($key, $val, $sec = null) {
        Cache::put($key, $val, $sec);
    }
}
