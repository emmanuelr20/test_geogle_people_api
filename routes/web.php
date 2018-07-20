<?php

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
// use RapidWeb\GoogleOAuth2Handler

Route::get('/', function () {
    return view('welcome');
});


Route::get('/get', 'TestController@index');
Route::get('/callback', 'TestController@callback')->name('callback');

