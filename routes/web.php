<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentITController;
use App\Http\Controllers\DocumentPacController;
use App\Http\Controllers\WebController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'Login'])->name('login');
Route::post('/login', [AuthController::class, 'LoginRequest'])->name('post.login');
Route::post('/logout', [AuthController::class, 'LogoutRequest'])->name('logout');
Route::group(['middleware' => 'auth'], function () {
    // Base Document
    Route::get('/', [WebController::class, 'myDocument'])->name('document.index');
    Route::get('/document/create', [WebController::class, 'createDocument'])->name('document.create');
    Route::get('/document/{document_type}/create', [WebController::class, 'createDocumentByType'])->name('document.create.type');
    Route::get('/document/{document_type}/view/{document_id}', [WebController::class, 'viewDocument'])->name('document.type.view');
    Route::post('/document/{document_type}/cancel/{document_id}', [WebController::class, 'cancelDocument'])->name('document.type.cancel');
    Route::get('/document/{document_type}/approve/{document_id}', [WebController::class, 'approveDocument'])->name('document.type.approve');
    Route::post('/document/{document_type}/approve/{document_id}', [WebController::class, 'approveDocumentRequest'])->name('document.type.approve.request');
    // Document Cancel
    // Document Files
    Route::get('/document/files/{file}', [WebController::class, 'fileShow'])->name('document.files.show');
    Route::get('/document/files/download/{file}', [WebController::class, 'fileDownload'])->name('document.files.download');
    // User Search
    Route::post('/user/search', [WebController::class, 'userSearch'])->name('user.search');

    // Create Document Inlucde PAC and HCLAB
    Route::post('/it/create', [DocumentITController::class, 'createDocument'])->name('document.it.create');

    // IT Document
    Route::prefix('it')->group(function () {
        // Page Documents
        Route::get('/admin/hardwaredocument', [DocumentITController::class, 'listHardwareDocuments'])->name('admin.it.hardwarelist');
        Route::get('/admin/approvelist', [DocumentITController::class, 'listApproveDocuments'])->name('admin.it.approvelist');
        Route::get('/admin/newdocument', [DocumentITController::class, 'listNewDocuments'])->name('admin.it.newlist');
        Route::get('/admin/mydocument', [DocumentITController::class, 'listMyDocuments'])->name('admin.it.mylist');
        Route::get('/admin/alldocument', [DocumentITController::class, 'listAllDocuments'])->name('admin.it.alllist');
        Route::get('/admin/view/{document_id}/{action}', [DocumentITController::class, 'viewDocument'])->name('admin.it.view');
        // Count IT Documents
        Route::get('/admin/count', [DocumentITController::class, 'listDocumentCount'])->name('admin.it.count');
        // Action Documents
        Route::post('/admin/hardware/approve', [DocumentITController::class, 'approveHardwareDocument'])->name('admin.it.hardware.approve');
        Route::post('/admin/accept', [DocumentITController::class, 'acceptDocument'])->name('admin.it.accept');
        Route::post('/admin/cancel', [DocumentITController::class, 'cancelDocument'])->name('admin.it.cancel');
        Route::post('/admin/canceljob', [DocumentITController::class, 'cancelJob'])->name('admin.it.canceljob');
        Route::post('/admin/process', [DocumentITController::class, 'processDocument'])->name('admin.it.process');
        Route::post('/admin/complete', [DocumentITController::class, 'completeDocument'])->name('admin.it.complete');
        Route::post('/admin/completeall', [DocumentITController::class, 'completeAllDocument'])->name('admin.it.completeall');
    });

    Route::prefix('pac')->group(function () {
        // Page Documents
        Route::get('/admin/approvelist', [DocumentPacController::class, 'listApproveDocuments'])->name('admin.pac.approvelist');
        Route::get('/admin/newdocument', [DocumentPacController::class, 'listNewDocuments'])->name('admin.pac.newlist');
        Route::get('/admin/mydocument', [DocumentPacController::class, 'listMyDocuments'])->name('admin.pac.mylist');
        Route::get('/admin/alldocument', [DocumentPacController::class, 'listAllDocuments'])->name('admin.pac.alllist');
        Route::get('/admin/view/{document_id}/{action}', [DocumentPacController::class, 'viewDocument'])->name('admin.pac.view');
        // Count IT Documents
        Route::get('/admin/count', [DocumentPacController::class, 'listDocumentCount'])->name('admin.it.count');
        // Action Documents
        Route::post('/admin/accept', [DocumentPacController::class, 'acceptDocument'])->name('admin.pac.accept');
        Route::post('/admin/cancel', [DocumentPacController::class, 'cancelDocument'])->name('admin.pac.cancel');
        Route::post('/admin/canceljob', [DocumentPacController::class, 'cancelJob'])->name('admin.it.canceljob');

    });
});
