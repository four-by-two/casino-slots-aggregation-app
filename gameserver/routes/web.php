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
$router->get('/', function () use ($router) {
  return 'hello';
});


$router->post('/test', function (Request $request) use ($router) {
    

    $script = '
    <script>
        function goBack() {
        window.history.back();
        }
    </script>';

    echo $script;
    echo '<script> goBack()</script>';
});