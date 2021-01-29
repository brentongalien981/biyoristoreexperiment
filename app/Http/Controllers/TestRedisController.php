<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TestRedisController extends Controller
{
    public function get(Request $request) {
        return Cache::store('redisreader')->get($request->key);
    }



    public function put(Request $request) {

        Cache::store('redisprimary')->put($request->key, $request->val);

        return "ok";
        
    }



    public function getConnection() {

        return "METHOD: getConnection()";
        
    }
}
