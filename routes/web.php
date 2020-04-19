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

$router->get('/', 'ExampleController@index');
$router->get('/telegram', 'ExampleController@sendNotifTelegram');

// Jatim
$router->get('/status', 'JatimController@status');
$router->get('/blitar', 'JatimController@blitar');

$router->get('/map/blitar', 'ShowBlitarRayaController@showBlitarRaya');
$router->get('/map/blitar/{village}', 'ShowBlitarRayaController@showVillage');