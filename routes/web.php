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

// $router->get('/', function () use ($router) {
    // $url['url'] = $_SERVER['HTTP_ORIGIN'];
    // return response($url);
//     return $router->app->version();
// });

$router->get('/', 'ExampleController@index');
$router->get('/blitar', 'ExampleController@blitar');
$router->get('/status', 'ExampleController@status');
$router->get('/telegram', 'ExampleController@sendNotifTelegram');


$router->get('/map/blitar', 'ShowBlitarRayaController@showBlitarRaya');
$router->get('/map/blitar/{village}', 'ShowBlitarRayaController@showVillage');
// $router->post('/<token>/webhook', function () {
//     $updates = Telegram::getWebhookUpdates();

//     return 'ok';
// });