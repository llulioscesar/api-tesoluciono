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
    return $router->app->version();
});

$router->group(['prefix' => 'v1'], function () use ($router){

    $router->get('user', 'UserController@index');
    $router->post('user', 'UserController@create');
    $router->put('user/{id}', 'UserController@update');
    $router->delete('user/{id}', 'UserController@delete');
    $router->post('user/auth', 'UserController@auth');

    $router->post('ally', 'AllyController@create');
    $router->post('ally/logo', 'AllyController@saveLogo');

    $router->get('category/{id}', 'CategoryController@get');

});
