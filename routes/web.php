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

Route::get('/', function () {
    return view('home', ['title' => 'SQLess']);
});

Route::get('/maple', function () {
    return view('maple_trial', ['title' => 'Try Maple - SQLess']);
})->name('trial');

Route::get('/maple2', function () {
   return view('maple_2_trial', ['title' => 'Try Maple 2']);
});

Route::get('/maple/docs', function () {
    return view('maple_manual', ['title' => 'Manual de usuario - Maple - SQLess']);
});

Route::get('/downloads/{platform}', 'DownloadsController@action');

Route::post('/maple', 'MapleController@parse');

Route::post('/maple2', 'Maple2Controller@call');
