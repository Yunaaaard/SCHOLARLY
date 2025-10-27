<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\AdminController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Admin
Route::get('/admin', [AdminController::class, 'index'])->name('admin');
Route::get('/admin-dashboard.php', function () { return redirect()->route('admin'); });

// Simple pages
Route::get('/settings', function () { if (!session()->has('user_id')) return redirect()->route('login'); return view('settings'); });
Route::get('/bookmarks', function () { if (!session()->has('user_id')) return redirect()->route('login'); return view('bookmarks'); });
Route::get('/notifications', function () { if (!session()->has('user_id')) return redirect()->route('login'); return view('notifications'); });
Route::get('/profile/edit', function () { if (!session()->has('user_id')) return redirect()->route('login'); return view('edit'); });

// API routes using web middleware (session)
Route::prefix('api')->group(function () {
    Route::get('/scholarships', [ApiController::class, 'scholarships']);
    Route::get('/scholarships/{id}', [ApiController::class, 'scholarships']);

    Route::get('/bookmarks', [ApiController::class, 'bookmarks']);
    Route::post('/bookmarks/toggle', [ApiController::class, 'toggleBookmark']);

    Route::match(['get','post'], '/applications', [ApiController::class, 'applications']);
    Route::match(['get','post'], '/user-profile', [ApiController::class, 'userProfile']);

    Route::match(['get','post'], '/notifications', [ApiController::class, 'notifications']);
    Route::match(['get','post'], '/admin/edit-scholarship', [ApiController::class, 'editScholarship']);
});

// Back-compat endpoints (optional while migrating)
Route::get('/api-scholarships.php', [ApiController::class, 'scholarships']);
Route::get('/api-scholarships.php/{id}', [ApiController::class, 'scholarships']);
Route::get('/api-bookmarks.php', [ApiController::class, 'bookmarks']);
Route::post('/api-bookmarks.php', [ApiController::class, 'toggleBookmark']);
Route::match(['get','post'], '/api-applications.php', [ApiController::class, 'applications']);
Route::match(['get','post'], '/api-user-profile.php', [ApiController::class, 'userProfile']);
Route::match(['get','post'], '/api-notifications.php', [ApiController::class, 'notifications']);
Route::match(['get','post'], '/api-edit-scholarship.php', [ApiController::class, 'editScholarship']);

// Back-compat logout
Route::get('/logout.php', [AuthController::class, 'logout']);
