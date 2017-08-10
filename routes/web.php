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
    return view('welcome');
});

Auth::routes();

Route::get('/admin/dashboard', 'AdminController@index');
Route::get('/admin/','AdminAuth\LoginController@redirectToAdminLogin');
Route::get('/admin/login','AdminAuth\LoginController@showLoginForm');
Route::post('/admin/login','AdminAuth\LoginController@login');
Route::post('/admin/logout','AdminAuth\LoginController@logout');
Route::post('/admin/password/email','AdminAuth\ForgotPasswordController@sendResetLinkEmail');
Route::get('/admin/password/reset' ,'AdminAuth\ForgotPasswordController@showLinkRequestForm');
Route::post('/admin/password/reset' ,'AdminAuth\ResetPasswordController@reset');
Route::get( '/admin/password/reset/{token}' ,'AdminAuth\ResetPasswordController@showResetForm');
Route::get('/admin/register' ,'AdminAuth\RegisterController@showRegistrationForm');
Route::post('/admin/register' ,'AdminAuth\RegisterController@register');

Route::get('/home', ['as' => 'user.home', 'uses' => 'HomeController@index']);
Route::get('/videochat', ['as' => 'user.videochat', 'uses' => 'HomeController@videochat']);