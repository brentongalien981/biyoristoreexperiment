<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

class TestPassportController extends Controller
{
    public function createPasswordAccessPassportToken(Request $request)
    {
        $email = 'bill@bs.com';
        $password = 'bill123';


        // $response = Http::asForm()->post('oauth/token', [
        //     'grant_type' => 'password',
        //     'client_id' => env('PASSPORT_GRANT_PASSWORD_CLIENT_ID'),
        //     'client_secret' => env('PASSPORT_GRANT_PASSWORD_CLIENT_SECRET'),
        //     'username' => $email,
        //     'password' => $password,
        //     'scope' => '',
        // ]);

        // return $response->json();




        // 3) Create Passport-Password-Access-Token.
        try {
            $request->request->add([
                'grant_type' => 'password',
                'client_id' => env('PASSPORT_GRANT_PASSWORD_CLIENT_ID'),
                'client_secret' => env('PASSPORT_GRANT_PASSWORD_CLIENT_SECRET'),
                'username' => $email,
                'password' => $password,
                'scope' => '*',
            ]);

            $tokenRequest = Request::create(
                url('oauth/token'),
                'post'
            );

            $response = Route::dispatch($tokenRequest);

            return $response;
        } catch (Exception $e) {
            return [
                'error bruh' => $e->getMessage(),
                'trace' => $e->getTrace(),
            ];
        }


        // $rawObjs = json_decode($response->original);

        // $oauthProps = [];
        // foreach ($rawObjs as $k => $v) {
        //     $oauthProps[$k] = $v;
        // }

        // return $oauthProps;
    }
}
