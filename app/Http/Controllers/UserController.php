<?php

namespace App\Http\Controllers;

use App\Http\BmdHelpers\BmdAuthProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    private const RESULT_CODE_OLD_PASSWORD_WRONG = ['code' => -1, 'msg' => 'old password is wrong'];



    public function update(Request $r)
    {
        $v = $r->validate([
            'oldPassword' => 'max:32',
            'newPassword' => 'max:32',
        ]);


        $isResultOk = false;
        $overallProcessLogs = ['In CLASS: UserController, METHOD: update()'];
        $resultCode = 0;

        $user = BmdAuthProvider::user();


        if (Hash::check($v['oldPassword'], $user->password)) {
            $overallProcessLogs[] = 'oldPassword ok';

            $user->password = Hash::make($v['newPassword']);
            $user->save();
            $overallProcessLogs[] = 'new password saved';

            $isResultOk = true;
            $resultCode = 1;
        } else {
            $overallProcessLogs[] = self::RESULT_CODE_OLD_PASSWORD_WRONG['msg'];
            $resultCode = self::RESULT_CODE_OLD_PASSWORD_WRONG['code'];
        }

        
        return [
            'isResultOk' => $isResultOk,
            'resultCode' => $resultCode,
            'overallProcessLogs' => $overallProcessLogs,            
        ];
    }
}
