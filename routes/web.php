<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/



/** socialite */
Route::middleware(['allow-frontend-referer-only', 'throttle:20,1'])->group(function () {
    // TODO:DEPLOYMENT: UNCOMMENT
    // Route::get('/bmd-socialite/signup-with-auth-provider', 'BmdSocialiteController@signupWithAuthProvider');
    // Route::get('/bmd-socialite/login-with-auth-provider', 'BmdSocialiteController@loginWithAuthProvider');

    // TODO:DEPLOYMENT: COMMENT-OUT
    Route::get('/bmd-socialite/signup-with-auth-provider', 'BmdSocialiteController@testsignupWithAuthProvider');
    Route::get('/bmd-socialite/login-with-auth-provider', 'BmdSocialiteController@testloginWithAuthProvider');
});
// TODO:ON-DEPLOYMENT: Tinker if you can add middleware on these to only allow callbacks from referer-urls facebook.com or google.com.
Route::get('/facebook/receive-socialite-auth-code', 'BmdSocialiteController@handleProviderCallbackFromFacebook');
Route::get('/google/receive-socialite-auth-code', 'BmdSocialiteController@handleProviderCallbackFromGoogle');




/** my-test-controller */
// FOR-DEBUG
// TODO:DEPLOYMENT: COMMENT-OUT
Route::get('/reviews/test2', 'ReviewController@test2');
Route::get('/reviews/test', 'ReviewController@test');
Route::get('/mytest/get-http-info', 'MyTestController@getHttpInfo');
Route::get('/mytest/flush-cache', 'MyTestController@flushCache');
Route::get('/test-redis/put', 'TestRedisController@put');
Route::get('/test-redis/get', 'TestRedisController@get');
Route::get('/test-redis/get-connection', 'TestRedisController@getConnection');



/** listing */
// FOR-DEBUG
// Route::get('/listing/test_readDataFromQueryWithPriceSort', 'ListingController@test_readDataFromQueryWithPriceSort');
// Route::get('/listing/test_stringReplace', 'ListingController@test_stringReplace');
// Route::get('/listing/test', 'ListingController@test');



// TODO:ON-DEPLOYMENT: COMMENT-OUT
Route::get('/mycache/has', 'MyCacheController@has');
Route::get('/mycache/test-get', 'MyCacheController@testGet');
Route::get('/mycache/test-put', 'MyCacheController@testPut');
Route::get('/', function () {
    return view('welcome');
});
Route::get('/hello', function () {
    return "hello";
});
Route::get('/yo', 'MyTestController@yo');

// Route::post('/payment-intent', 'PaymentIntentController@create');
