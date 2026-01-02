<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuizController;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [QuizController::class, 'index'])->name('quiz.index');
Route::post('/submit', [QuizController::class, 'submit'])->name('quiz.submit');
Route::get('/analyse-ergebnis', [QuizController::class, 'showEmailForm'])->name('quiz.email');
Route::post('/download-pdf', [QuizController::class, 'generatePDF'])->name('quiz.download');

// TEMPORARY MIGRATION ROUTE
Route::get('/setup-migration', function() {
    try {
        Artisan::call('migrate --force');
        return 'Migration done: ' . Artisan::output();
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});

Route::get('/reset-db', function() {
    try {
        Artisan::call('migrate:fresh --force');
        return 'DATABASE RESET DONE! <br>' . Artisan::output();
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});


Route::get('/admin/leads', [App\Http\Controllers\AdminController::class, 'index'])->name('admin.index');


// --- DEBUG HELPERS ---

// 1. Clear Cache (Essential after .env changes)
Route::get('/clear-all-caches', function() {
    try {
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        return 'All Caches cleared! <br>Config, Cache, View, Route. <br><a href="/">Back to App</a>';
    } catch (\Exception $e) {
        return 'Error clearing cache: ' . $e->getMessage();
    }
});

// 2. Test Session (To see if cookies work)
Route::get('/debug-session', function() {
    session(['test_time' => date('Y-m-d H:i:s')]);
    return 'Session value set at ' . date('H:i:s') . '. <br><a href="/debug-session-check">Click to verify Session</a>';
});

Route::get('/debug-session-check', function() {
    if (session()->has('test_time')) {
        return 'SUCCESS: Session works! Value: ' . session('test_time');
    }
    return 'ERROR: Session is empty. Cookies or permissions issue.';
});

// 3. Better Log Viewer
Route::get('/debug-log', function() {
    $path = storage_path('logs/laravel.log');
    if (!file_exists($path)) {
        return 'Log file not found at: ' . $path;
    }
    $content = file_get_contents($path);
    $lines = explode("\n", $content);
    // Get last 100 lines and REVERSE them (newest top)
    $lastLines = array_reverse(array_slice($lines, -100));
    
    return '<h1>Last 100 Log Entries (Newest First)</h1><pre>' . implode("\n", $lastLines) . '</pre>';
});
