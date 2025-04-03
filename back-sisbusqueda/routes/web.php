<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LibroController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
use App\Http\Controllers\CustomAccessTokenController;

Route::get('/', function () {
    return view('welcome');
});


Route::resource('libros', LibroController::class);
Route::get('/reporte', function () {
    return view('reporte');
});

