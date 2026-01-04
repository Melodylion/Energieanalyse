<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuizController;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\QuizManagerController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- 1. QUIZ (PUBLIC) ---

// Dynamic Quiz Routes (Slug-based)
Route::get('/quiz/{slug}', [QuizController::class, 'index'])->name('quiz.show');
Route::post('/quiz/{slug}/submit', [QuizController::class, 'submit'])->name('quiz.submit');

// Default Route (Homepage - Fallback to 'energieanalyse')
Route::get('/', [QuizController::class, 'index'])->name('quiz.index');

// Results & PDF
Route::get('/analyse-ergebnis', [QuizController::class, 'showEmailForm'])->name('quiz.email');
Route::post('/download-pdf', [QuizController::class, 'generatePDF'])->name('quiz.download');


// --- 2. AUTHENTICATION ---
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');


// --- 3. PROTECTED ADMIN AREA ---
Route::middleware('auth')->group(function () {
    
    // Dashboard & Actions
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::delete('/admin/respondents/{respondent}', [AdminController::class, 'destroy'])->name('admin.respondents.destroy');
    
    // Legacy Redirect
    Route::get('/admin/leads', function() { return redirect()->route('admin.dashboard'); });

    // CMS / Quiz Manager
    Route::resource('admin/quizzes', QuizManagerController::class, ['as' => 'admin']);
    
    // Import / Export
    Route::get('admin/quizzes/{quiz}/export', [App\Http\Controllers\ImportExportController::class, 'export'])->name('admin.export');
    Route::get('admin/import', [App\Http\Controllers\ImportExportController::class, 'showImportForm'])->name('admin.import.form');
    Route::post('admin/import', [App\Http\Controllers\ImportExportController::class, 'import'])->name('admin.import.process');

    // User Management (Admins)
    Route::resource('admin/users', \App\Http\Controllers\UserController::class, ['as' => 'admin']);

    // Categories Manager (Nested)
    Route::post('/admin/quizzes/{quiz}/categories', [App\Http\Controllers\CategoryController::class, 'store'])->name('admin.categories.store');
    Route::put('/admin/categories/{category}', [App\Http\Controllers\CategoryController::class, 'update'])->name('admin.categories.update');
    Route::delete('/admin/categories/{category}', [App\Http\Controllers\CategoryController::class, 'destroy'])->name('admin.categories.destroy');

    // Admin Tools
    Route::get('/admin/migrate', [AdminController::class, 'runMigrate'])->name('admin.migrate'); // Dangerous but useful
    Route::get('/admin/clear-cache', function() {
        Artisan::call('optimize:clear');
        return 'Cache cleared! <a href="' . route('admin.dashboard') . '">Back to Dashboard</a>';
    })->name('admin.clear-cache');
});
