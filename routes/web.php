<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', 'WelcomeController@index')->name('home');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('dashboard');

Route::post('/search', 'WelcomeController@search')->name('search');

Route::get('/opportunities/{opportunityId}', 'OpportunitiesController@show')->name('opportunities.show');

Route::get('/apply/{opportunityId}', 'HomeController@apply')->name('apply');

Route::group(['namespace' => 'Admin', 'as' => 'admin.', 'prefix' => 'admin'], function() {

    Route::group(['middleware' => 'role'], function() {
        Route::get('/', 'AdminController@index')->name('home');
        Route::get('/home', 'AdminController@index')->name('dashboard');
        Route::post('/search', 'AdminController@search')->name('search');
        Route::get('opportunity/{opportunityId}/show', 'AdminController@getShow');
        Route::get('opportunity/{opportunityId}/edit', 'AdminController@getEdit');
        Route::post('opportunity/{opportunityId}/edit', 'AdminController@postEdit');
        Route::get('opportunity/{opportunityId}/delete', 'AdminController@getDelete');
        Route::post('opportunity/{opportunityId}/delete', 'AdminController@postDelete');
    });

    Route::get('login', 'LoginController@showLogin')->name('showLogin');
    Route::post('login', 'LoginController@login')->name('login');
});
