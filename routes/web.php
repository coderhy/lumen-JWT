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
$router->get('test','ExampleController@test');
$router->get('foo', function () {
    return 'Hello World';
});

$router->post('login','AuthController@login');
$router->group(['prefix'=>'/','middleware'=>'auth:api'],function () use ($router){
   	$router->post('logout','AuthController@logout');
    $router->post('refresh','AuthController@refreshToken');
    $router->post('me','AuthController@me');
});
