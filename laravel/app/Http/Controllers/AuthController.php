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
            Session::put('first_name', $user->first_name ?? '');
            Session::put('last_name', $user->last_name ?? '');
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

    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = trim($request->input('email'));

        // Check if email exists
        $user = DB::table('users')->where('email', $email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email not found. Please check again.'])->withInput();
        }

        // Generate token
        $token = bin2hex(random_bytes(16));
        $expiry = now()->addMinutes(15);

        // Ensure password_resets table exists
        DB::statement("CREATE TABLE IF NOT EXISTS password_resets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token VARCHAR(255) NOT NULL,
            expires_at DATETIME NOT NULL
        )");

        // Delete old tokens for this user
        DB::table('password_resets')->where('user_id', $user->id)->delete();

        // Store token
        DB::table('password_resets')->insert([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => $expiry,
        ]);

        // Build reset link
        $resetLink = url('/reset-password?token=' . $token);

        // Send email using PHPMailer
        try {
            require_once public_path('phpmailer/PHPMailer.php');
            require_once public_path('phpmailer/SMTP.php');
            require_once public_path('phpmailer/Exception.php');

            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'scholarlys2@gmail.com';
            $mail->Password = 'avuu xpqo qfez qkob';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('scholarlys2@gmail.com', 'Scholarly Support');
            $mail->addAddress($email);

            // Email content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body = "
                <p>Hello,</p>
                <p>You requested a password reset for your Scholarly account.</p>
                <p>Click the link below to reset your password:</p>
                <p><a href='$resetLink'>$resetLink</a></p>
                <p>This link expires in 15 minutes.</p>
                <p>If you did not request this, please ignore this email.</p>
            ";

            $mail->send();

            return back()->with('success', 'A password reset link has been sent to your email.');
        } catch (\Exception $e) {
            // For development, you might want to show the link
            // In production, just show a generic message
            return back()->with('success', 'A password reset link has been sent to your email. (Development: ' . $resetLink . ')');
        }
    }

    public function showResetPasswordForm(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            return redirect()->route('forgot-password')->with('error', 'Invalid reset link.');
        }

        // Check if token is valid
        $reset = DB::table('password_resets')
            ->where('token', $token)
            ->where('expires_at', '>=', now())
            ->first();

        if (!$reset) {
            return redirect()->route('forgot-password')->with('error', 'Invalid or expired reset link. Please request a new one.');
        }

        return view('auth.reset-password', ['token' => $token]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $token = $request->input('token');
        $password = $request->input('password');

        // Check token validity
        $reset = DB::table('password_resets')
            ->where('token', $token)
            ->where('expires_at', '>=', now())
            ->first();

        if (!$reset) {
            return redirect()->route('forgot-password')->with('error', 'Invalid or expired reset link. Please request a new one.');
        }

        // Update user password
        DB::table('users')
            ->where('id', $reset->user_id)
            ->update(['password' => Hash::make($password)]);

        // Delete token (prevent reuse)
        DB::table('password_resets')->where('token', $token)->delete();

        return redirect()->route('login')->with('success', 'Password reset successful! Please log in with your new password.');
    }
}
