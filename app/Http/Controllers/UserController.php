<?php

namespace App\Http\Controllers;

use App\AuthProviderType;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\BmdHelpers\BmdAuthProvider;

class UserController extends Controller
{
    private const RESULT_CODE_OLD_PASSWORD_WRONG = ['code' => -1, 'msg' => 'old password is wrong'];
    private const RESULT_CODE_USER_PROVIDER_NOT_ALLOWED = ['code' => -2, 'msg' => 'This action is not supported.'];



    public function update(Request $r)
    {
        $v = $r->validate([
            'oldPassword' => 'max:32',
            'newPassword' => 'max:32',
        ]);


        $isResultOk = false;
        $overallProcessLogs = ['In CLASS: UserController, METHOD: update()'];
        $resultCode = 0;


        if (BmdAuthProvider::bmdAuth()->auth_provider_type_id != AuthProviderType::BMD) {
            $overallProcessLogs[] = self::RESULT_CODE_USER_PROVIDER_NOT_ALLOWED['msg'];
            $resultCode = self::RESULT_CODE_USER_PROVIDER_NOT_ALLOWED['code'];
        } else {

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
        }


        return [
            'isResultOk' => $isResultOk,
            'resultCode' => $resultCode,
            // 'overallProcessLogs' => $overallProcessLogs,            
        ];
    }
}
