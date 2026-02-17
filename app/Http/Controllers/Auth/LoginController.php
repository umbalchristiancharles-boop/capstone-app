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
        if ($user && \Illuminate\Support\Facades\Hash::check($password, $user->password)) {
            // Store user info in session
            Session::put('user_id', $user->id);
            Session::put('user_role', $user->role);
            Session::put('user_department', $user->department ?? null);
            Session::put('user_name', $user->full_name);

            // Redirect based on role
            if ($user->role === 'ADMIN') {
                return redirect()->route('admin.dashboard')
                    ->with('success', 'Welcome back, Admin!');
            } elseif ($user->role === 'OWNER') {
                return redirect()->route('owner.dashboard')
                    ->with('success', 'Welcome back, Owner!');
            } elseif ($user->role === 'BRANCH_MANAGER') {
                return redirect()->route('manager.dashboard')
                    ->with('success', 'Welcome back, Manager!');
            } elseif ($user->role === 'STAFF') {
                // Department-based staff panels
                if ($user->department === 'CASHIER') {
                    return redirect()->route('staff.cashier.dashboard')
                        ->with('success', 'Welcome back, Cashier!');
                } elseif ($user->department === 'FINANCE') {
                    return redirect()->route('staff.finance.dashboard')
                        ->with('success', 'Welcome back, Finance Staff!');
                } elseif ($user->department === 'INVENTORY') {
                    return redirect()->route('staff.inventory.dashboard')
                        ->with('success', 'Welcome back, Inventory Staff!');
                } else {
                    return redirect()->route('staff.dashboard')
                        ->with('success', 'Welcome back, Staff!');
                }
            } elseif (in_array($user->department, ['HR', 'FINANCE', 'INVENTORY', 'LOGISTICS'])) {
                // Manager panels by department
                return redirect()->route('manager.' . strtolower($user->department) . '.dashboard')
                    ->with('success', 'Welcome back, ' . $user->department . ' Manager!');
            }
        }

        return back()
            ->withInput($request->only('username'))
            ->withErrors(['login' => 'Invalid username or password. ']);
    }

    public function logout(\Illuminate\Http\Request $request)
    {
        // Clear session data and prevent session fixation
        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Logged out successfully.');
    }
}
