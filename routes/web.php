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

// welcome
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// home
// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// simulador
// Route::get('simulador/index', 'App\Http\Controllers\SimulatorController@index')->name('simulador.index');
Route::get('simulador/create', 'App\Http\Controllers\SimulatorController@create')->name('simulador.create');
Route::post('simulador/store', 'App\Http\Controllers\SimulatorController@store')->name('simulador.store');
Route::get('simulador/resultado', 'App\Http\Controllers\SimulatorController@resultado')->name('simulador.resultado');
Route::get('simulador/resultado/pdf', 'App\Http\Controllers\SimulatorController@exportPdf')->name('simulador.resultado.export');
