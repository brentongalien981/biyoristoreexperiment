<?php

namespace App\MyHelpers\General;

use EasyPost\EasyPost;
use Exception;

class GeneralHelper2
{
    public static function pseudoJsonify($var)
    {
        switch (gettype($var)) {
            case 'array':
            case 'object':
                return self::jsonifyObj($var);
            case 'boolean':
            case 'integer':
            case 'double':
            case 'string':
            default:
                return $var;
        }
    }



    public static function jsonifyObj($obj)
    {
        $jsonifiedObj = [];

        foreach ($obj as $k => $v) {
            $simplifiedV = self::pseudoJsonify($v);
            $jsonifiedObj[$k] = $simplifiedV;
        }

        return $jsonifiedObj;
    }



    public static function setEasyPostApiKey()
    {

        $apiKey = env('EASYPOST_TK');
        $appEnv = env('APP_ENV');

        switch ($appEnv) {
            case 'staging':
            case 'production':
            case 'deployment':
                // BMD-ON-ITER: Staging, Deployment, Production:
                // Comment this out.
                // throw new Exception('Trying to use EP-PK');
                $apiKey = env('EASYPOST_PK');
                break;
        }


        EasyPost::setApiKey($apiKey);
    }



    public static function getAppFrontendUrl()
    {
        $appEnv = env('APP_ENV');

        switch ($appEnv) {
            case 'prestaging':
                return env('APP_FRONTEND_URL_FOR_PRESTAGING');
            case 'staging':
                return env('APP_FRONTEND_URL_FOR_STAGING');
            case 'production':
            case 'deployment':
                return env('APP_FRONTEND_URL_FOR_DEPLOYMENT');
            default:
                return env('APP_FRONTEND_URL_FOR_DEVELOPMENT');
        }
    }



    public static function getAppFrontendUrl2()
    {
        return env('APP_FRONTEND_URL');
    }



    public static function getStripeInstanceBasedOnAppEnv()
    {
        if (env('APP_ENV') === 'production') {
            // BMD-ON-ITER: Staging, Deployment: Edit this.
            throw new Exception('Trying to use Stripes Primary Key!');
            // return new \Stripe\StripeClient(env('STRIPE_PK'));
        }

        return new \Stripe\StripeClient(env('STRIPE_SK'));
    }
}
