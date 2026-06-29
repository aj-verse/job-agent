<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ResumeController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Authentication Routes (Guests only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Logout Route (Authenticated only)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Application Routes
Route::middleware('auth')->group(function () {
    // Redirect root to dashboard
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    // Dashboard Route
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Resume Upload & Profile Routes
    Route::prefix('resume')->group(function () {
        Route::get('/', [ResumeController::class, 'index'])->name('resume.index');
        Route::post('/upload', [ResumeController::class, 'upload'])->name('resume.upload');
        Route::put('/update', [ResumeController::class, 'update'])->name('resume.update');
    });

    // Jobs Management Routes
    Route::prefix('jobs')->group(function () {
        Route::get('/queue', [JobController::class, 'queue'])->name('jobs.queue');
        Route::get('/history', [JobController::class, 'history'])->name('jobs.history');
        
        // Action triggers
        Route::post('/discover', [JobController::class, 'discover'])->name('jobs.discover');
        Route::post('/apply/{id}', [JobController::class, 'apply'])->name('jobs.apply');
        Route::post('/auto-apply', [JobController::class, 'autoApply'])->name('jobs.autoApply');
    });

    // AI Career Recommendations
    Route::get('/recommendations', [RecommendationController::class, 'index'])->name('recommendations.index');

    // Configuration Settings
    Route::prefix('settings')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/update', [SettingController::class, 'update'])->name('settings.update');
        Route::post('/authenticate', [SettingController::class, 'authenticate'])->name('settings.authenticate');
    });
});

// Scheduler webhook (for free-tier deployment on platforms like Render)
Route::match(['get', 'post'], '/api/scheduler/run', function (\Illuminate\Http\Request $request) {
    $apiKey = env('SCHEDULER_API_KEY');
    if (empty($apiKey)) {
        return response()->json(['error' => 'Scheduler API key is not configured.'], 500);
    }
    
    $token = $request->header('X-Scheduler-Token') ?? $request->input('token');
    if ($token !== $apiKey) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    try {
        // Run the Laravel scheduler
        \Illuminate\Support\Facades\Artisan::call('schedule:run');
        $output = \Illuminate\Support\Facades\Artisan::output();
        return response()->json([
            'success' => true,
            'message' => 'Scheduler executed successfully.',
            'output' => trim($output)
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Scheduler error: ' . $e->getMessage()
        ], 500);
    }
});
