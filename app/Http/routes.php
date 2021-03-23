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

Route::get('start', ['as' => 'points.start', 'uses' => 'ClientsController@startCode']);
Route::post('check', ['as' => 'points.check', 'uses' => 'ClientsController@checkCode']);
Route::post('update', ['as' => 'points.update', 'uses' => 'ClientsController@updateLocation']);

Route::any('clients/add', ['as' => 'clients.add', 'uses' => 'ClientsController@add']);

Route::name('admin.')->middleware('auth')->group(function () {
    Route::any('admin/points', ['as' => 'points', 'uses' => 'PointsController@admin']);
    Route::any('admin/clients', ['as' => 'clients', 'uses' => 'ClientsController@admin']);

	Route::get('admin/codes', function () {
		return view('codes');
	});
});


