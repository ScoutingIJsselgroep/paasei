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

Auth::routes(['register' => false]);

Route::get('/', ['as' => 'clients.search', 'uses' => 'ClientsController@search']);

Route::get('start', ['as' => 'clients.start', 'uses' => 'ClientsController@start']);
Route::get('c/{code}', ['uses' => 'ClientsController@check']);
Route::post('check', ['as' => 'clients.check', 'uses' => 'ClientsController@check']);

Route::any('clients/add', ['as' => 'clients.add', 'uses' => 'ClientsController@add']);

Route::name('admin.')->middleware('auth')->group(function () {
    Route::any('admin/points', ['as' => 'points', 'uses' => 'PointsController@admin']);
    Route::any('admin/clients', ['as' => 'clients', 'uses' => 'ClientsController@admin']);

	Route::get('admin/codes', function () {
		return view('codes');
	});
});


