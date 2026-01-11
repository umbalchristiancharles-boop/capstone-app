<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $username = $request->input('username');
        $password = $request->input('password');

        // Find user by username
        $user = DB::table('users')
            ->where('username', $username)
            ->where('is_active', 1)
            ->first();

        // Check if user exists and password matches
        if ($user && $user->password_hash === $password) {
            // Store user info in session
            Session::put('user_id', $user->id);
            Session::put('user_role', $user->role);
            Session::put('user_name', $user->full_name);

            // Redirect based on role
            if ($user->role === 'OWNER') {
                return redirect()->route('admin.dashboard')
                    ->with('success', 'Welcome back, Admin!');
            } elseif ($user->role === 'STAFF') {
                return redirect()->route('staff.dashboard')
                    ->with('success', 'Welcome back, ' . $user->full_name .  '!');
            } elseif ($user->role === 'BRANCH_MANAGER') {
                return redirect()->route('manager.dashboard')
                    ->with('success', 'Welcome back, Manager!');
            }
        }

        return back()
            ->withInput($request->only('username'))
            ->withErrors(['login' => 'Invalid username or password. ']);
    }

    public function logout()
    {
        Session::flush();
        return redirect()->route('login')->with('success', 'Logged out successfully.');
    }
}
