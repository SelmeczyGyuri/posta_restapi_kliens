<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CitiesController;
use App\Http\Controllers\CountiesController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('counties.index');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/counties', [CountiesController::class, 'index'])->name('counties.index');
Route::get('/counties/create', [CountiesController::class, 'create'])->name('counties.create');
Route::get('/counties/{id}', [CountiesController::class, 'show'])->name('counties.show');
Route::get('/counties/{id}/edit', [CountiesController::class, 'edit'])->name('counties.edit');
Route::post('/counties', [CountiesController::class, 'store'])->name('counties.store');
Route::put('/counties/{id}', [CountiesController::class, 'update'])->name('counties.update');
Route::delete('/counties/{id}', [CountiesController::class, 'destroy'])->name('counties.destroy');

Route::get('/cities', [CitiesController::class, 'index'])->name('cities.index');
Route::get('/cities/create', [CitiesController::class, 'create'])->name('cities.create');
Route::get('/cities/{id}', [CitiesController::class, 'show'])->name('cities.show');
Route::get('/cities/{id}/edit', [CitiesController::class, 'edit'])->name('cities.edit');
Route::post('/cities', [CitiesController::class, 'store'])->name('cities.store');
Route::put('/cities/{id}', [CitiesController::class, 'update'])->name('cities.update');
Route::delete('/cities/{id}', [CitiesController::class, 'destroy'])->name('cities.destroy');