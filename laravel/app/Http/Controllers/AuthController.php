<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username_email' => 'required|string',
            'password' => 'required|string',
        ]);

        $usernameEmail = trim($request->input('username_email'));
        $password = $request->input('password');

// Legacy admin hardcoded login
        if ($usernameEmail === 'admin' && $password === '123456') {
            Session::put('is_admin', true);
            Session::put('username', 'admin');
            return redirect()->intended(route('admin'));
        }

        $user = DB::table('users')
            ->where('username', $usernameEmail)
            ->orWhere('email', $usernameEmail)
            ->first();

        if ($user && Hash::check($password, $user->password)) {
            Session::put('user_id', $user->id);
            Session::put('username', $user->username);
            Session::put('email', $user->email);

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['username_email' => 'Invalid username/email or password'])->withInput();
    }

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|min:3',
            'email' => 'required|email',
            'contact' => 'required',
            'password' => 'required|min:6|confirmed',
            'terms' => 'accepted',
        ]);

        $username = trim($request->input('username'));
        $email = trim($request->input('email'));
        $contact = trim($request->input('contact'));
        $password = $request->input('password');

        // Ensure uniqueness like legacy - fix the query to check both conditions properly
        $exists = DB::table('users')
            ->where(function($query) use ($username, $email) {
                $query->where('username', $username)
                      ->orWhere('email', $email);
            })
            ->exists();

        if ($exists) {
            // Check which one exists
            $usernameExists = DB::table('users')->where('username', $username)->exists();
            $emailExists = DB::table('users')->where('email', $email)->exists();
            
            if ($usernameExists && $emailExists) {
                return back()->withErrors(['username' => 'Username and email already exist'])->withInput();
            } elseif ($usernameExists) {
                return back()->withErrors(['username' => 'Username already exists'])->withInput();
            } else {
                return back()->withErrors(['email' => 'Email already exists'])->withInput();
            }
        }

        // Insert user data - don't include updated_at as legacy table may not have it
        $userData = [
            'username' => $username,
            'email' => $email,
            'contact' => $contact,
            'password' => Hash::make($password),
            'created_at' => now(),
        ];
        
        $id = DB::table('users')->insertGetId($userData);

        Session::put('user_id', $id);
        Session::put('username', $username);
        Session::put('email', $email);

        return redirect()->route('dashboard');
    }

    public function logout()
    {
        Session::flush();
        return redirect()->route('login');
    }
}
