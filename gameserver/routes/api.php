<?php
use Illuminate\Http\Request;

/** @var \Laravel\Lumen\Routing\Router $router */

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
// Auth
$router->group(['prefix' => 'api'], function () use ($router) {
$router->post('auth/login', ['as' => 'auth.login', 'uses' => 'AuthController@login']);
$router->get('auth/me', ['as' => 'auth.me', 'uses' => 'AuthController@me']);
$router->post('auth/register', ['as' => 'auth.register', 'uses' => 'AuthController@register']);
$router->post('auth/logout', ['as' => 'auth.logout', 'uses' => 'AuthController@logout']);
});
