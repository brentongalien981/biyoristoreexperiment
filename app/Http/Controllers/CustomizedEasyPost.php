<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CustomizedEasyPost extends Controller
{
    public function getRates()
    {

        \EasyPost\EasyPost::setApiKey(env('EASYPOST_TK'));

        $fromAddress = \EasyPost\Address::create(array(
            'company' => 'EasyPost',
            'street1' => '417 Montgomery Street',
            'street2' => '5th Floor',
            'city' => 'San Francisco',
            'state' => 'CA',
            'zip' => '94104',
            'phone' => '415-528-7555'
        ));

        $jsFromAddres = [];
        foreach ($fromAddress as $k => $v) {
            $jsFromAddres[$k] = $v;
        }


        return [
            'msg' => 'In CLASS: CustomizedEasyPost, METHOD: getRates()',
            'obj' => $jsFromAddres
        ];
    }
}
