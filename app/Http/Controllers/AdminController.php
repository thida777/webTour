<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * Show the admin login form.
     */
    public function showLogin()
    {
        return view('admin.login');
    }

    /**
     * Check the email and password against the users table.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            // New session id after login (protects against session fixation).
            $request->session()->regenerate();

            return redirect('/admin/dashboard');
        }

        return back()
            ->withErrors(['email' => 'The email or password is incorrect.'])
            ->onlyInput('email');
    }

    /**
     * Show the admin dashboard with basic statistics.
     */
    public function dashboard()
    {
        $totalPackages = Package::count();
        $totalMessages = Contact::count();
        $unreadMessages = Contact::where('is_read', false)->count();

        return view('admin.dashboard', compact('totalPackages', 'totalMessages', 'unreadMessages'));
    }

    /**
     * Log the admin out and destroy the session.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }
}
