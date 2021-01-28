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



/** listing */
// FOR-DEBUG
// Route::get('/listing/test_readDataFromQueryWithPriceSort', 'ListingController@test_readDataFromQueryWithPriceSort');
// Route::get('/listing/test_stringReplace', 'ListingController@test_stringReplace');
// Route::get('/listing/test', 'ListingController@test');



Route::get('/mycache/has', 'MyCacheController@has');
Route::get('/mycache/test-get', 'MyCacheController@testGet');
Route::get('/mycache/test-put', 'MyCacheController@testPut');



Route::get('/', function () {
    return view('welcome');
});

Route::get('/hello', function () {
    return "hello";
});

// Route::post('/payment-intent', 'PaymentIntentController@create');
