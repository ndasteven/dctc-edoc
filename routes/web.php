<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\UserPermissionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentEditor;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\PdfView;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LicenceController;
use App\Http\Controllers\serviceFolder;
use Illuminate\Support\Facades\Request;
use App\Models\Folder;

Route::get('/newAccount', [RegisterController::class, 'index'])->middleware('checklicence')->name('register');
Route::post('/Account/store', [RegisterController::class, 'store'])->middleware('checklicence')->name('user.new');
Route::post('/licence/verify', [LicenceController::class, 'verify'])->name('licence.verify');

// Groupes de routes authentifiées avec middlewares appropriés
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->middleware('checklicence')->name('dashboard');
    Route::get('/service', [ServiceController::class, 'index'])->middleware('checklicence')->name('service');
    Route::post('/service/store', [ServiceController::class, 'store'])->middleware('checklicence')->name('service.store');
    Route::get('/utilisateur', [UserController::class, 'index'])->middleware('checklicence', 'checkrole')->name('user');
    Route::get('/service/{id}', [ServiceController::class, 'show'])->middleware('checklicence')->name('service.show');
    Route::get('/utilisateur/{id}', [UserController::class, 'show'])->middleware('checklicence', 'checkrole')->name('user.show');
    Route::post('/utilisateur/store', [UserController::class, 'store'])->middleware('checklicence', 'checkrole')->name('user.store');
    Route::post('/utilisateur/store_role', [UserController::class, 'store_role'])->middleware('checklicence', 'checkrole')->name('user.store_role');
    Route::get('/utilisateur/{id}/edit', [UserController::class, 'edit'])->middleware('checklicence', 'checkrole')->name('users.edit');
    Route::put('/utilisateur/{id}', [UserController::class, 'update'])->middleware('checklicence', 'checkrole')->name('users.update');
    Route::delete('/utilisateur/{id}', [UserController::class, 'destroy'])->middleware('checklicence', 'checkrole')->name('users.destroy');
    Route::get('/document', [DocumentController::class, 'index'])->middleware('checklicence')->name('document');
    Route::get('/profile/{id}', [UserController::class, 'show_profile'])->middleware('checklicence')->name('profile');
    Route::put('/update_profile/{id}', [UserController::class, 'update_profile'])->middleware('checklicence')->name('user.update_profile');
    Route::post('/user/update_password', [UserController::class, 'update_password'])->middleware('checklicence')->name('user.update_password');
    Route::delete('/service/{id}', [ServiceController::class, 'destroy'])->middleware('checklicence')->name('service.destroy');
    Route::put('/service_update/{id}', [ServiceController::class, 'update'])->middleware('checklicence')->name('service.update');

    // Routes qui nécessitent une vérification d'accès au service
    Route::get('/documents/{service}', [DocumentController::class, 'getDocuments'])
        ->middleware('checklicence', 'checkservice')
        ->name('documents.show');

    Route::get('/api/users', [UserController::class, 'search'])->middleware('checklicence');
    Route::get('/tag/{id}', [TagController::class, 'index'])->middleware('checklicence')->name('tag');
    Route::post('/tag/store', [TagController::class, 'store'])->middleware('checklicence')->name('tag.store');
    Route::get('/message', [MessageController::class, 'index'])->middleware('checklicence')->name('message');
    Route::get('/message/{pivotId}', [MessageController::class, 'show'])->middleware('checklicence')->name('message.show');
    Route::get('/messageSended/{pivotId}', [MessageController::class, 'showSend'])->middleware('checklicence')->name('message.showSend');
    Route::post('/indentification/store/{id}', [ServiceController::class, 'identUser'])->middleware('checklicence')->name('service.ident');
    Route::get('/pdf/{id}', [PdfView::class, 'index'])->middleware('checklicence')->name('pdf.view');
    Route::delete('/document/{id}', [DocumentController::class, 'destroy'])->middleware('checklicence')->name('documents.destroy');
    Route::get('/historique', [HistoryController::class, 'index'])->middleware('checklicence')->name('history');
    Route::get('/export-historque-pdf', [HistoryController::class, 'exportPDF'])->middleware('checklicence')->name('history.export');
    Route::get('/api/users/search', [UserController::class, 'searchForSelect2'])->name('users.searchForSelect2');
    Route::get('/editer/{id}', [DocumentEditor::class, 'index'])->name('documents.edit');

    Route::get('/test', function () {
        return redirect()->route('folders.show', ['folder' => null]);
    });

    Route::get('/folders/{folderId?}', function (?int $folderId = null) {
        return view('folder-view', ['folderId' => $folderId]);
    })->name('folders.show');

    // Route pour l'affichage des dossiers - nécessite une vérification d'accès au service
    Route::get('/documentsFolder/{service}', [serviceFolder::class, 'getFolderService'])
        ->middleware('checklicence', 'checkservice')
        ->name('show_docs');
});

// Routes pour les rappels
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Route personnalisée pour la liste des rappels (avant apiResource pour éviter le conflit)
    Route::get('reminders', function () {
        return view('reminders-list');
    })->name('reminders.index');

    Route::get('reminders/list', function () {
        return view('reminders-list');
    })->name('reminders.list');

    Route::apiResource('reminders', \App\Http\Controllers\ReminderController::class)->except(['index']);
    Route::get('reminders/document/{documentId}', [\App\Http\Controllers\ReminderController::class, 'getByDocument'])->name('reminders.byDocument');
    Route::get('reminders/folder/{folderId}', [\App\Http\Controllers\ReminderController::class, 'getByFolder'])->name('reminders.byFolder');
    Route::get('reminders/user', [\App\Http\Controllers\ReminderController::class, 'getUserReminders'])->name('reminders.user');
    Route::get('reminders/upcoming', [\App\Http\Controllers\ReminderController::class, 'getUpcomingReminders'])->name('reminders.upcoming');
    Route::get('reminders/upcoming-count', [\App\Http\Controllers\ReminderController::class, 'getUpcomingForNotification'])->name('reminders.upcoming.count');
    Route::post('reminders/{id}/complete', [\App\Http\Controllers\ReminderController::class, 'complete'])->name('reminders.complete');
});

// =================================== route ajoute pas kevin N'doufou
