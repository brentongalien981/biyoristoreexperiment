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



/* checkout */
Route::middleware('auth:api')->post('/checkout/readCheckoutRequiredData', 'CheckoutController@readCheckoutRequiredData');



/* payment */
Route::middleware('auth:api')->post('/stripePaymentMethod/save', 'StripePaymentMethodController@save');
Route::middleware('auth:api')->post('/stripePaymentMethod/update', 'StripePaymentMethodController@update');
Route::post('/paymentIntent', 'PaymentIntentController@create');



/* payment-info */
Route::middleware('auth:api')->post('/paymentInfo/save', 'PaymentInfoController@save');



/* cartItem */
Route::middleware('auth:api')->post('/cartItem/update', 'CartItemController@update');
Route::middleware('auth:api')->post('/cartItem/delete', 'CartItemController@destroy');
Route::middleware('auth:api')->post('/cartItem/save', 'CartItemController@save');



/* cart */
Route::middleware('auth:api')->get('/cart/show', 'CartController@show');



/* address */
Route::middleware('auth:api')->post('/address/destroy', 'AddressController@destroy');
Route::middleware('auth:api')->post('/address/save', 'AddressController@save');




/* products */
// Random comment for testing git branch "Items@show@brentongalien981".
Route::get('/products/featured', 'ProductController@featured');
Route::get('/products/relatedProducts', 'ProductController@relatedProducts');
Route::get('/products', 'ProductController@index');
Route::get('/products/show', 'ProductController@show');



/* brands */
Route::get('/brands', 'BrandController@index');



/* categories */
Route::get('/categories', 'CategoryController@index');



/* join */
Route::post('/join/save', 'JoinController@save');
Route::post('/join/login', 'JoinController@login');



/* profile */
Route::middleware('auth:api')->get('/profile/show', 'ProfileController@show');
Route::middleware('auth:api')->post('/profile/save', 'ProfileController@save');



/* test */
// fruitcake/laravel-cors middleware setup.
Route::get('/test', function (Request $request) {
    return [
        'isResultOk' => true,
        'url' => '/test',
        'comment' => 'random shit bruh'
    ];
});