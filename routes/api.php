<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



/** bmd-auth */
Route::post('/bmd-auth/trySalvageToken', 'BmdAuthController@trySalvageToken')->middleware('bmdauth');
Route::post('/bmd-auth/flagAsExpiring', 'BmdAuthController@flagAsExpiring')->middleware('bmdauth');
Route::post('/bmd-auth/checkBmdAuthValidity', 'BmdAuthController@checkBmdAuthValidity')->middleware('bmdauth');



/* customized-easypost */
Route::get('/customized-easypost/checkCartItems', 'CustomizedEasyPost@checkCartItems');
Route::get('/customized-easypost/getRates', 'CustomizedEasyPost@getRates');
Route::get('/customized-easypost/test', 'CustomizedEasyPost@test');



/* order */
Route::post('/orders/read', 'OrderController@read')->middleware('bmdauth');
Route::get('/orders/{id}', 'OrderController@show');
Route::post('/orders', 'OrderController@index')->middleware('bmdauth');


/* checkout */
Route::post('/checkout/doOrderInventoryChecks', 'CheckoutController@doOrderInventoryChecks');
Route::post('/checkout/finalizeOrderWithPredefinedPayment', 'CheckoutController@finalizeOrderWithPredefinedPayment')->middleware('bmdauth');
Route::post('/checkout/finalizeOrder', 'CheckoutController@finalizeOrder');
Route::middleware('bmdauth')->post('/checkout/readCheckoutRequiredData', 'CheckoutController@readCheckoutRequiredData');



/** exchange-rates */
Route::get('/exchange-rates/getRate', 'ExchangeRateController@getRate');



/* payment */
Route::post('/stripePaymentMethod/delete', 'StripePaymentMethodController@delete')->middleware('bmdauth');
Route::post('/stripePaymentMethod/save', 'StripePaymentMethodController@save')->middleware('bmdauth');
Route::post('/stripePaymentMethod/update', 'StripePaymentMethodController@update')->middleware('bmdauth');
Route::post('/paymentIntent', 'PaymentIntentController@create');



/* payment-info */
Route::middleware('auth:api')->post('/paymentInfo/save', 'PaymentInfoController@save');



/* cart */
Route::post('/cart/mergeGuestAndActualUserCarts', 'CartController@mergeGuestAndActualUserCarts')->middleware('bmdauth');
Route::post('/cart/tryExtendingCartLifespan', 'CartController@tryExtendingCartLifespan')->middleware('bmdauth');
Route::get('/cart/tryExtendingCartLifespan', 'CartController@tryExtendingCartLifespan');

Route::post('/cart/deleteCartItem', 'CartController@deleteCartItem')->middleware('bmdauth');
Route::get('/cart/deleteCartItem', 'CartController@deleteCartItem');
Route::post('/cart/updateCartItemCount', 'CartController@updateCartItemCount')->middleware('bmdauth');
Route::get('/cart/updateCartItemCount', 'CartController@updateCartItemCount');
Route::post('/cart/updateUserCartCache', 'CartController@updateUserCartCache')->middleware('bmdauth');
Route::post('/cart/addItem', 'CartController@addItem')->middleware('bmdauth');
Route::get('/cart/addItem', 'CartController@addItem');
Route::post('/cart/read', 'CartController@read')->middleware('bmdauth');
Route::get('/cart/read', 'CartController@read');



/* address */
Route::post('/address/destroy', 'AddressController@destroy')->middleware('bmdauth');
Route::post('/address/save', 'AddressController@save')->middleware('bmdauth');



/** reviews */
Route::middleware('bmdauth')->post('/reviews/save', 'ReviewController@save');
Route::get('/reviews/read', 'ReviewController@read');



/* products */
// Random comment for testing git branch "Items@show@brentongalien981".
Route::get('/products/featured', 'ProductController@featured');
Route::get('/products/relatedProducts', 'ProductController@relatedProducts');
Route::get('/products', 'ProductController@index');
Route::get('/products/show', 'ProductController@show');


/* listing */
Route::get('/listing/read-products', 'ListingController@readProducts');
Route::get('/listing/read-filters', 'ListingController@readFilters');



/* brands */
Route::get('/brands', 'BrandController@index');



/* categories */
Route::get('/categories', 'CategoryController@index');



/* join */
Route::post('/join/verify', 'JoinController@verify')->middleware('bmdauth');
Route::post('/join/save', 'JoinController@save');
Route::post('/join/login', 'JoinController@login');
Route::post('/join/emailUserResetLink', 'JoinController@emailUserResetLink');
Route::post('/join/updatePassword', 'JoinController@updatePassword');



/* profile */
Route::post('/profile/show', 'ProfileController@show')->middleware('bmdauth');
Route::post('/profile/save', 'ProfileController@save')->middleware('bmdauth');



/** user */
Route::post('/users/update', 'UserController@update')->middleware('bmdauth');



/** returns */
Route::post('/returns/requestForReturn', 'ReturnController@requestForReturn');



/** search */
Route::get('/search', 'SearchController@index');



/* test */
// BMD-FOR-DEBUG
// BMD-ON-STAGING: Comment-out.
// BMD-TAGS: test, testing, debug, tinker, cors, http, https, auth
// fruitcake/laravel-cors middleware setup.
/*
Route::get('/test', function (Request $request) {
    return [
        'isResultOk' => true,
        'url' => '/test',
        'comment' => 'random shit bruh'
    ];
});
*/
Route::get('/mytest/forMBMDBE', 'MyTestController@forMBMDBE');
Route::get('/mytest/get-http-info', 'MyTestController@getHttpInfo')->middleware('allow-frontend-only');
Route::post('/mytest/testbmdauth', 'MyTestController@testbmdauth')->middleware('bmdauth');