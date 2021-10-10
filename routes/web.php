<?php

use App\Mail\OrderReceived;
use App\Mail\PasswordResetLink;
use App\Order;
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
    // BMD-ON-STAGING: UNCOMMENT
    // Route::get('/bmd-socialite/signup-with-auth-provider', 'BmdSocialiteController@signupWithAuthProvider');
    // Route::get('/bmd-socialite/login-with-auth-provider', 'BmdSocialiteController@loginWithAuthProvider');

    // BMD-ON-STAGING: COMMENT-OUT
    Route::get('/bmd-socialite/signup-with-auth-provider', 'BmdSocialiteController@testsignupWithAuthProvider');
    Route::get('/bmd-socialite/login-with-auth-provider', 'BmdSocialiteController@testloginWithAuthProvider');
});
// BMD-ON-STAGING: Tinker if you can add middleware on these to only allow callbacks from referer-urls facebook.com or google.com.
Route::get('/facebook/receive-socialite-auth-code', 'BmdSocialiteController@handleProviderCallbackFromFacebook');
Route::get('/google/receive-socialite-auth-code', 'BmdSocialiteController@handleProviderCallbackFromGoogle');




/** test-passport */
// BMD-ON-STAGING: COMMENT-OUT
Route::get('/testpassport/create-token', 'TestPassportController@createPasswordAccessPassportToken');



/** my-test-controller */
// BMD-TAGS: cache, redis, test, testing, debug
// BMD-FOR-DEBUG
// BMD-ON-STAGING: COMMENT-OUT
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



// BMD-ON-STAGING: COMMENT-OUT
// BMD-FOR-DEBUG
// BMD-TAGS: cache, redis, test, testing, debug
Route::get('/mycache/has', 'MyCacheController@has');
Route::get('/mycache/test-get', 'MyCacheController@testGet');
Route::get('/mycache/test-put', 'MyCacheController@testPut');
/*
Route::get('/', function () {
    return view('welcome');
});
*/

// BMD-ON-ITER: Staging, Deployment: Comment-out.
Route::get('/test-render-mailable', function () {
    // $o = Order::first();
    // $o = Order::find('7193216c-443a-4af1-a6ec-dfb8f7b5548c');
    // return (new OrderReceived($o))->render();
    // return new PasswordResetLink();
});

