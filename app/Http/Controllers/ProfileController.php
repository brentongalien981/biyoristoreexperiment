<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProfileResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function save(Request $request)
    {
        //
        $validatedData = $request->validate([
            'email' => 'email|min:8|max:64|unique:users',
            'phone' => 'nullable|numeric'
        ]);


        //
        return [
            'validatedData' => $validatedData,
            'message' => "In CLASS: ProfileController, METHOD: save()"
        ];
    }



    public function show(Request $request)
    {
        return [
            'profile' => new ProfileResource(Auth::user()->profile)
        ];
    }
}
