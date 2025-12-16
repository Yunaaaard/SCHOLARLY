<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\AdminController;

Route::get('/', function () {
    return view('index');
})->name('index');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('forgot-password');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot-password.post');

Route::get('/reset-password', [AuthController::class, 'showResetPasswordForm'])->name('reset-password');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('reset-password.post');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Terms and Conditions
Route::get('/terms-and-conditions', function () {
    return view('terms-and-conditions');
})->name('terms');

// Admin
Route::get('/admin', [AdminController::class, 'index'])->name('admin');
Route::get('/admin-dashboard.php', function () { return redirect()->route('admin'); });

// Admin - Add Scholarship
Route::get('/admin/add-scholarship', [AdminController::class, 'showAddScholarship'])->name('admin.add-scholarship');
Route::post('/admin/add-scholarship', [AdminController::class, 'addScholarship'])->name('admin.add-scholarship.post');

// Admin - Student Management
Route::get('/admin/students', [AdminController::class, 'studentManagement'])->name('admin.students');
Route::post('/admin/students/delete', [AdminController::class, 'deleteStudent'])->name('admin.students.delete');
Route::get('/admin/students/details', [AdminController::class, 'getStudentDetails'])->name('admin.students.details');

// Back-compat routes
Route::get('/add-scholarship.php', function () { return redirect()->route('admin.add-scholarship'); });
Route::get('/studentmanagementboard.php', function () { return redirect()->route('admin.students'); });

// Simple pages - with profile picture loading
Route::get('/settings', function () { 
  if (!session()->has('user_id')) return redirect()->route('login'); 
  $userId = session('user_id');
  $user = DB::table('users')->select('profile_picture')->where('id', $userId)->first();
  $profilePicture = $user->profile_picture ?? null;
  if ($profilePicture && !str_starts_with($profilePicture, 'http')) {
    $profilePicture = url('/') . '/' . $profilePicture;
  }
  return view('settings', ['profile_picture' => $profilePicture]); 
});
Route::get('/bookmarks', function () { 
  if (!session()->has('user_id')) return redirect()->route('login'); 
  $userId = session('user_id');
  $user = DB::table('users')->select('profile_picture')->where('id', $userId)->first();
  $profilePicture = $user->profile_picture ?? null;
  if ($profilePicture && !str_starts_with($profilePicture, 'http')) {
    $profilePicture = url('/') . '/' . $profilePicture;
  }
  return view('bookmarks', ['profile_picture' => $profilePicture]); 
});
Route::get('/notifications', function () { 
  if (!session()->has('user_id')) return redirect()->route('login'); 
  $userId = session('user_id');
  $user = DB::table('users')->select('profile_picture')->where('id', $userId)->first();
  $profilePicture = $user->profile_picture ?? null;
  if ($profilePicture && !str_starts_with($profilePicture, 'http')) {
    $profilePicture = url('/') . '/' . $profilePicture;
  }
  return view('notifications', ['profile_picture' => $profilePicture]); 
});
Route::get('/profile/edit', function () { 
  if (!session()->has('user_id')) return redirect()->route('login'); 
  $userId = session('user_id');
  $user = DB::table('users')->select('profile_picture')->where('id', $userId)->first();
  $profilePicture = $user->profile_picture ?? null;
  if ($profilePicture && !str_starts_with($profilePicture, 'http')) {
    $profilePicture = url('/') . '/' . $profilePicture;
  }
  return view('edit', ['profile_picture' => $profilePicture]); 
});

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

// Back-compat forgot password
Route::get('/forgotpassword.php', function () { return redirect()->route('forgot-password'); });
Route::post('/forgotpassword.php', [AuthController::class, 'forgotPassword']);
Route::get('/resetpassword.php', [AuthController::class, 'showResetPasswordForm']);
Route::post('/resetpassword.php', [AuthController::class, 'resetPassword']);
