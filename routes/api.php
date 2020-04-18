<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/page', 'PageController@create');
    Route::put('/page/{page}', 'PageController@update');

    Route::get('/page/{page}/post', 'PostController@get');
    Route::post('/page/{page}/post', 'PostController@create');
    
    Route::post('/post/{post}', 'PostController@edit');
    Route::delete('/post/{post}', 'PostController@delete');
});

