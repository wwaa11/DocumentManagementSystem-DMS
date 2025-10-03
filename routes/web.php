<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentITController;
use App\Http\Controllers\WebController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'Login'])->name('login');
Route::post('/login', [AuthController::class, 'LoginRequest'])->name('post.login');
Route::post('/logout', [AuthController::class, 'LogoutRequest'])->name('logout');

Route::group(['middleware' => 'auth'], function () {
    Route::get('/', [WebController::class, 'myDocument'])->name('document.index');
    Route::get('/document/create', [WebController::class, 'createDocument'])->name('document.create');
    Route::get('/document/{document_type}/create', [WebController::class, 'createDocumentByType'])->name('document.create.type');

    Route::post('/user/search', [WebController::class, 'userSearch'])->name('user.search');

    Route::prefix('it')->group(function () {
        Route::post('/create', [DocumentITController::class, 'createDocument'])->name('document.it.create');
    });
});
