<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return response()->json([
       "api" => [
           "status" => "ok",
           "version" => 1
       ]
    ]);
});

$router->group(['prefix' => 'v1'], function () use ($router){

    $router->get('user', ['middleware' => 'auth', 'uses' => 'UserController@index']);
    $router->post('user', ['middleware' => 'auth', 'uses' => 'UserController@create']);
    $router->put('user/{id}', ['middleware' => 'auth', 'uses' => 'UserController@update']);
    $router->delete('user/{id}', ['middleware' => 'auth', 'uses' => 'UserController@delete']);
    $router->post('auth', 'UserController@auth');
    $router->post('auth/renew', 'UserController@renew');
    $router->post('auth/password', 'UserController@resetPass');
    $router->get('auth/password/{token}', 'UserController@enableResetPass');
    $router->put('auth/password/new/{id}', 'UserController@newPass');

    $router->post('ally', ['middleware' => 'auth', 'uses' => 'AllyController@create']);
    $router->post('ally/logo', ['middleware' => 'auth', 'uses' => 'AllyController@saveLogo']);
    $router->put('ally/{id}', ['middleware' => 'auth', 'uses' => 'AllyController@update']);
    $router->delete('ally/{id}', ['middleware' => 'auth', 'uses' => 'AllyController@delete']);
    $router->get('ally', 'AllyController@index');

    $router->get('category/{id}', ['middleware' => 'auth', 'uses' => 'CategoryController@get']);
    $router->post('category', ['middleware' => 'auth', 'uses' => 'CategoryController@create']);
    $router->put('category/{id}', ['middleware' => 'auth', 'uses' => 'CategoryController@update']);
    $router->delete('category/{id}', ['middleware' => 'auth', 'uses' => 'CategoryController@delete']);
    $router->get('category', 'CategoryController@index');

});
