<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentITController;
use App\Http\Controllers\DocumentTrainingController;
use App\Http\Controllers\DocumentUserController;
use App\Http\Controllers\WebController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'Login'])->name('login');
Route::post('/login', [AuthController::class, 'LoginRequest'])->name('post.login');
Route::post('/logout', [AuthController::class, 'LogoutRequest'])->name('logout');

// HRD download files
Route::get('/training/download-pdf/{id}', [DocumentTrainingController::class, 'downloadPDF'])->name('document.training.downloadPDF');

Route::group(['middleware' => 'auth'], function () {
    // Admin
    Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
        Route::get('/approver/list', [AdminController::class, 'ApproverList'])->name('approvers.list');
        Route::post('/approver/getuser', [AdminController::class, 'ApproverGetUser'])->name('approvers.getuser');
        Route::post('/approver/update', [AdminController::class, 'ApproverUpdate'])->name('approvers.update');

        Route::get('/role/list', [AdminController::class, 'RoleList'])->name('roles.list');
        Route::post('/role/update', [AdminController::class, 'RoleUpdate'])->name('roles.update');
    });

    // Base Create Document
    Route::get('/', [WebController::class, 'myDocument'])->name('document.index');
    Route::get('/document/create', [WebController::class, 'createDocument'])->name('document.create');
    Route::get('/document/{document_type}/create', [WebController::class, 'createDocumentByType'])->name('document.create.type');
    Route::get('/document/{document_type}/view/{document_id}', [WebController::class, 'viewDocument'])->name('document.type.view');
    Route::post('/document/{document_type}/cancel/{document_id}', [WebController::class, 'cancelDocument'])->name('document.type.cancel');
    Route::get('/document/{document_type}/approve/{document_id}', [WebController::class, 'approveDocument'])->name('document.type.approve');
    Route::post('/document/{document_type}/approve/{document_id}', [WebController::class, 'approveDocumentRequest'])->name('document.type.approve.request');
    Route::get('/document/files/{file}', [WebController::class, 'fileShow'])->name('document.files.show');
    Route::get('/document/files/download/{file}', [WebController::class, 'fileDownload'])->name('document.files.download');
    Route::post('/user/search', [WebController::class, 'userSearch'])->name('user.search');

    // Create Document IT and User for PAC HC Heartstream Register
    Route::post('/it/create', [DocumentITController::class, 'createDocument'])->name('document.it.create');
    Route::post('/it/return', [DocumentITController::class, 'borrowReturn'])->name('document.it.borrowlist.return');
    // IT Document
    Route::prefix('it')->middleware(['auth', 'it'])->group(function () {
        // Page Documents
        Route::get('/admin/hardwaredocument', [DocumentITController::class, 'adminHardwareDocuments'])->name('admin.it.hardwarelist');
        Route::get('/admin/approvelist', [DocumentITController::class, 'adminApproveDocuments'])->name('admin.it.approvelist');
        Route::get('/admin/newdocument', [DocumentITController::class, 'adminNewDocuments'])->name('admin.it.newlist');
        Route::get('/admin/mydocument', [DocumentITController::class, 'adminMyDocuments'])->name('admin.it.mylist');
        Route::get('/admin/alldocument', [DocumentITController::class, 'adminAllDocuments'])->name('admin.it.alllist');
        Route::get('/admin/view/{type}/{document_id}/{action}', [DocumentITController::class, 'adminviewDocument'])->name('admin.it.view');
        // Count IT Documents
        Route::get('/admin/count/{type}', [DocumentITController::class, 'adminDocumentCount'])->name('admin.it.count');
        // Action Documents
        Route::post('/admin/hardware/approve', [DocumentITController::class, 'approveHardwareDocument'])->name('admin.it.hardware.approve');
        Route::post('/admin/accept', [DocumentITController::class, 'acceptDocument'])->name('admin.it.accept');
        Route::post('/admin/cancel', [DocumentITController::class, 'cancelDocument'])->name('admin.it.cancel');
        Route::post('/admin/canceljob', [DocumentITController::class, 'cancelJob'])->name('admin.it.canceljob');
        Route::post('/admin/process', [DocumentITController::class, 'processDocument'])->name('admin.it.process');
        Route::post('/admin/complete', [DocumentITController::class, 'completeDocument'])->name('admin.it.complete');
        Route::post('/admin/completeall', [DocumentITController::class, 'completeAllDocument'])->name('admin.it.completeall');
        // Borrow
        Route::get('/admin/borrowlist', [DocumentITController::class, 'adminBorrowDocuments'])->name('admin.it.borrowlist');
        Route::post('/admin/borrowlist/add', [DocumentITController::class, 'adminBorrowAdd'])->name('admin.it.borrowlist.add');
        Route::post('/admin/borrowlist/remove', [DocumentITController::class, 'adminBorrowRemove'])->name('admin.it.borrowlist.remove');
        Route::post('/admin/borrowlist/summary', [DocumentITController::class, 'adminBorrowSummary'])->name('admin.it.borrowlist.summary');
        Route::post('/admin/borrowlist/approve', [DocumentITController::class, 'adminBorrowApprove'])->name('admin.it.borrowlist.approve');
        Route::post('/admin/borrowlist/retrieve', [DocumentITController::class, 'adminBorrowRetrieve'])->name('admin.it.borrowlist.retrieve');
        // Report
        Route::get('/admin/reportlist', [DocumentITController::class, 'adminReportDocuments'])->name('admin.it.reportlist');
    });
    // HC , Heart Stream, PAC, Register
    Route::prefix('user')->middleware(['auth', 'user'])->group(function () {
        // Page Documents
        Route::get('/admin/{type}/approvelist/', [DocumentUserController::class, 'adminApproveDocuments'])->name('admin.user.approvelist');
        Route::get('/admin/{type}/newdocument', [DocumentUserController::class, 'adminNewDocuments'])->name('admin.user.newlist');
        Route::get('/admin/{type}/mydocument', [DocumentUserController::class, 'adminMyDocuments'])->name('admin.user.mylist');
        Route::get('/admin/{type}/alldocument', [DocumentUserController::class, 'adminAllDocuments'])->name('admin.user.alllist');
        Route::get('/admin/{type}/view/{document_id}/{action}', [DocumentUserController::class, 'viewDocument'])->name('admin.user.view');
        // Count User Documents
        Route::get('/admin/count/{type}', [DocumentUserController::class, 'adminDocumentCount'])->name('admin.user.count');
        // Action Documents
        Route::post('/admin/accept', [DocumentUserController::class, 'acceptDocument'])->name('admin.user.accept');
        Route::post('/admin/cancel', [DocumentUserController::class, 'cancelDocument'])->name('admin.user.cancel');
        Route::post('/admin/canceljob', [DocumentUserController::class, 'cancelJob'])->name('admin.user.canceljob');
        Route::post('/admin/process', [DocumentUserController::class, 'processDocument'])->name('admin.user.process');
        Route::post('/admin/complete', [DocumentUserController::class, 'completeDocument'])->name('admin.user.complete');
        Route::post('/admin/completeall', [DocumentUserController::class, 'completeAllDocument'])->name('admin.user.completeall');
        // Report
        Route::get('/admin/reportlist', [DocumentUserController::class, 'adminReportDocuments'])->name('admin.user.reportlist');
    });

    // Training
    Route::post('/training/create', [DocumentTrainingController::class, 'createDocument'])->name('document.training.create');
    Route::post('/training/create-training', [DocumentTrainingController::class, 'createProject'])->name('document.training.createTraining');
    Route::post('/training/get-attendance', [DocumentTrainingController::class, 'getAttendance'])->name('document.training.getAttendance');
    Route::post('/training/approve-attendance', [DocumentTrainingController::class, 'approveAttendance'])->name('document.training.approveAttendance');
    Route::post('/training/close-project', [DocumentTrainingController::class, 'closeProject'])->name('document.training.closeProject');
    Route::post('/training/save-assessment', [DocumentTrainingController::class, 'saveAssessment'])->name('document.training.saveAssessment');
    Route::get('/training/download-pdf/{id}', [DocumentTrainingController::class, 'downloadPDF'])->name('document.training.downloadPDF');

});
