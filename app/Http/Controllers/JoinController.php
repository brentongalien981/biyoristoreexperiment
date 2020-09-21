<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class JoinController extends Controller
{
    public function save(Request $request)
    {
        //
        $validatedData = $request->validate([
            'email' => 'email|min:8|max:64|unique:users',
            'password' => 'alpha_num|min:4|max:32',
        ]);


        //
        $user = User::create([
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        $apiToken = Str::random(80);

        $user->forceFill([
            'api_token' => hash('sha256', $apiToken),
        ])->save();


        return [
            'isResultOk' => true,
            'comment' => "CLASS: JoinController, METHOD: save()",
            'validatedData' => $validatedData,
            'email' => $validatedData['email'],
            'apiToken' => $apiToken
        ];
    }
}
