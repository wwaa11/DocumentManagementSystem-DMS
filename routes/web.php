<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\WebController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'Login'])->name('login');
Route::post('/login', [AuthController::class, 'LoginRequest'])->name('post.login');
Route::post('/logout', [AuthController::class, 'LogoutRequest'])->name('logout');

Route::group(['middleware' => 'auth'], function () {
    Route::get('/index', [WebController::class, 'Index'])->name('index');
});
