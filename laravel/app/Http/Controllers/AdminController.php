<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        if (!session()->get('is_admin')) {
            return redirect()->route('login');
        }
        return view('admin.dashboard');
    }
}
