<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Simple auth check using legacy session keys
        if (!session()->has('user_id')) {
            return redirect()->route('login');
        }

        return view('dashboard');
    }
}
