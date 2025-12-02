<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Simple auth check using legacy session keys
        if (!session()->has('user_id')) {
            return redirect()->route('login');
        }

        // Get user profile picture to prevent flash
        $userId = session('user_id');
        $user = DB::table('users')
            ->select('profile_picture')
            ->where('id', $userId)
            ->first();

        $profilePicture = $user->profile_picture ?? null;
        if ($profilePicture && !str_starts_with($profilePicture, 'http')) {
            $profilePicture = url('/') . '/' . $profilePicture;
        }

        return view('dashboard', ['profile_picture' => $profilePicture]);
    }
}
