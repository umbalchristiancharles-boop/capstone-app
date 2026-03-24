<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\CustomerAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Debug: log credentials and password hash
        $user = User::where('username', '=', $credentials['username'])->first();
        Log::debug('Login attempt', [
            'username' => $credentials['username'],
            'input_password' => $credentials['password'],
            'db_password' => $user ? $user->password : null,
            'user_exists' => $user ? true : false,
        ]);

        if (!Auth::attempt($credentials)) {    // uses getAuthPassword() if defined
            Log::debug('Auth::attempt failed', [
                'username' => $credentials['username'],
                'input_password' => $credentials['password'],
                'db_password' => $user ? $user->password : null,
            ]);
            return response()->json([
                'ok'      => false,
                'message' => 'Invalid username or password',
            ], 401);
        }

        $request->session()->regenerate();     // prevent session fixation [web:4][web:6]

        $user = Auth::user();
        /** @var \App\Models\User|null $user */

        if (!$user) {
            Log::debug('Auth::user() returned null after attempt', [
                'username' => $credentials['username'],
            ]);
            return response()->json([
                'ok' => false,
                'message' => 'User not found',
            ], 401);
        }

        // CRITICAL: Validate that user's account is active BEFORE allowing login
        if (!$user->is_active) {
            Log::warning('Login attempt from inactive account', [
                'user_id' => $user->id,
                'username' => $user->username,
                'role' => $user->role,
            ]);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'ok' => false,
                'message' => 'Your account has been deactivated. Please contact support.',
            ], 403);
        }

        // Validate role exists and is valid
        // Include all expected roles: SUPER_ADMIN, ADMIN, OWNER, MANAGER, MANAGER_HR, HR, STAFF
$validRoles = ['SUPER_ADMIN', 'ADMIN', 'OWNER', 'MANAGER', 'MANAGER_HR', 'HR', 'STAFF', 'SUPPLIER'];
        $roleUpper = strtoupper(trim($user->role ?? ''));

        // Handle MANAGER_HR role specially - treat as MANAGER with HR department
        if ($roleUpper === 'MANAGER_HR') {
            $roleUpper = 'MANAGER';
            // Set department to HR if not already set
            if (empty($user->department)) {
                $user->department = 'HR';
                // Validate user is a valid User model instance before accessing properties
                if ($user instanceof User) {
                    $user->save();
                } else {
                    // User is not a valid instance, log error with safe access
                    Log::error('Invalid user object during MANAGER_HR department assignment');
                }
            }
        }

        if (!in_array($roleUpper, $validRoles)) {
            Log::error('Unknown or invalid role detected during login', [
                'user_id' => $user->id,
                'username' => $user->username,
                'role' => $user->role,
            ]);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'ok' => false,
                'message' => 'System configuration error. Please contact support.',
            ], 500);
        }

        // Check if user must change password - redirect to password change page
        if ($user->must_change_password) {
            $redirectPath = '/change-password';
            Log::debug('Login successful - password change required', [
                'username' => $user->username,
                'id' => $user->id,
                'role' => $user->role,
                'redirect_path' => $redirectPath,
            ]);
        } else {
            // Determine the normal redirect path based on role
            $redirectPath = $this->getRedirectPath($user);

            Log::debug('Login successful - role-based redirect', [
                'username' => $user->username,
                'id' => $user->id,
                'role' => $user->role,
                'department' => $user->department,
                'redirect_path' => $redirectPath,
            ]);
        }

        // Keep legacy session keys in sync for routes that rely on Session::has('user_id')
        Session::put('user_id', $user->id);
        Session::put('user_role', $user->role);
        Session::put('user_name', $user->full_name);
        // Store the authoritative redirect path in session
        Session::put('redirect_path', $redirectPath);

        // Create Sanctum token for API authentication
        /** @var \App\Models\User $user */
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'ok' => true,
            'message' => 'Login successful',
            'redirect_path' => $redirectPath,
            'token' => $token, // Send token to frontend for storage
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'role' => strtolower($user->role),
                'department' => strtolower($user->department ?? ''),
                'full_name' => $user->full_name,
                'branch_id' => $user->branch_id,
                'must_change_password' => (bool) $user->must_change_password,
            ],
        ]);
    }

    /**
     * Determine the correct redirect path based on user role and department.
     * This is the server-side authoritative source for routing.
     */
    private function getRedirectPath($user)
    {
        $role = strtoupper(trim($user->role ?? ''));
        $department = strtoupper(trim($user->department ?? ''));

        // Handle MANAGER_HR - treat as MANAGER with HR department
        if ($role === 'MANAGER_HR') {
            $role = 'MANAGER';
            $department = 'HR';
        }

        // OWNER - highest privilege
        if ($role === 'OWNER') {
            return '/owner-panel';
        }

        // SUPER_ADMIN - direct to super-admin dashboard
        if ($role === 'SUPER_ADMIN') {
            return '/super-admin/dashboard';
        }

        // ADMIN
        if ($role === 'ADMIN') {
            return '/admin-panel';
        }

        // HR
        if ($role === 'HR') {
            return '/hr-panel';
        }

        // MANAGER - check department for specific panel
        if ($role === 'MANAGER') {
            if ($department === 'INVENTORY') {
                return '/manager/inventory';
            }
            if ($department === 'FINANCE') {
                return '/manager/finance';
            }
            if ($department === 'PROCUREMENT') {
                return '/manager/procurement';
            }
            if ($department === 'LOGISTICS') {
                return '/manager/logistics';
            }
            if ($department === 'HR') {
                return '/manager/hr';
            }
            // Default manager panel
            return '/manager-panel';
        }

        // STAFF - check department for specific panel
        if ($role === 'STAFF') {
            if ($department === 'KITCHEN') {
                return '/staff/kitchen';
            }
            if ($department === 'INVENTORY') {
                return '/staff/inventory';
            }
            if ($department === 'CASHIER') {
                return '/staff/cashier';
            }
            if ($department === 'FINANCE') {
                return '/staff/finance';
            }
            if ($department === 'LOGISTICS') {
                return '/staff/logistics';
            }
            // Default staff panel
            return '/staff-panel';
        }

        // SUPPLIER
        if ($role === 'SUPPLIER') {
            return '/supplier-panel';
        }

        // Fallback
        return '/staff-landing?error=unknown_role';
    }


    public function logout(Request $request)
    {
        Auth::logout();
        Session::forget(['user_id', 'user_role', 'user_name', 'redirect_path']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'ok' => true,
            'message' => 'Logout successful',
        ]);
    }

    public function me(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);
        if (! $user) {
            return response()->json([
                'ok'      => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        return response()->json([
            'ok'   => true,
            'user' => $user,
        ]);
    }

    public function profile(Request $request)
    {
        $u = $this->resolveAuthenticatedUser($request);
        if (! $u) {
            return response()->json([
                'ok'      => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        return response()->json([
            'ok'   => true,
            'user' => [
                'id'        => $u->id,
                'username'  => $u->username,
                'role'      => $u->role,
                'full_name' => $u->full_name,
                'email'     => $u->email,
                'must_change_password' => (bool) $u->must_change_password,
            ],
        ]);
    }

    public function changePassword(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);

        // Require proper authentication - no username-based fallback allowed
        if (! $user) {
            return response()->json([
                'ok'      => false,
                'message' => 'Unauthenticated. Please login to change your password.',
            ], 401);
        }

        // Server-side validation only - password regex not exposed to frontend
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return response()->json([
                'ok' => false,
                'message' => 'Current password is incorrect',
            ], 400);
        }

        $user->update([
            'password' => $request->input('new_password'), // Mutator will hash this
            'must_change_password' => false,
        ]);

        // If user has an email and it's not verified, either send a one-time verification code
        // or auto-verify for supplier accounts (created via procurement panel).
        $verificationSent = false;

        $roleUpper = strtoupper(trim($user->role ?? ''));
        $isBsupplierUser = isset($user->username) && Str::startsWith(strtolower($user->username), 'bsupplier');
        $skipVerification = ($roleUpper === 'SUPPLIER' || $isBsupplierUser);

        if (!empty($user->email) && is_null($user->email_verified_at)) {
            if ($skipVerification) {
                // Auto-verify supplier emails because procurement panel already emailed credentials
                try {
                    $user->email_verified_at = now();
                    $user->save();
                    Cache::forget('verification_code_' . $user->email);
                    Log::info('Auto-verified supplier email after password change for user id ' . $user->id);
                } catch (\Exception $e) {
                    Log::error('Failed to auto-verify supplier email after password change: ' . $e->getMessage());
                }
            } else {
                try {
                    $email = $user->email;
                    $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                    Cache::put('verification_code_' . $email, $code, 600); // 10 minutes

                    Mail::raw(
                        "Your CHIKIN TAYO verification code is: {$code}\n\nThis code will expire in 10 minutes.",
                        function ($message) use ($email) {
                            $message->to($email)
                                    ->subject('CHIKIN TAYO - Email Verification Code');
                        }
                    );

                    Log::info("Verification email sent to {$email} after password change");
                    $verificationSent = true;
                } catch (\Exception $e) {
                    Log::error('Failed to send verification email after password change: ' . $e->getMessage());
                    // Do not fail the password change if email sending fails
                }
            }
        }

        return response()->json([
            'ok' => true,
            'message' => 'Password updated successfully',
            'verification_sent' => $verificationSent,
        ]);
    }

    public function ownerProfile(Request $request)
    {
        $u = $this->resolveAuthenticatedUser($request);
        if (! $u) {
            return response()->json([
                'ok'      => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        // Generate full absolute URL for avatar if it exists
        $avatarUrl = null;
        if ($u->avatar_url) {
            // If avatar_url doesn't start with http, prepend the full URL
            if (strpos($u->avatar_url, 'http') === 0) {
                $avatarUrl = $u->avatar_url;
            } else {
                $avatarUrl = url($u->avatar_url);
            }
        }

        return response()->json([
            'ok'   => true,
            'user' => [
                'id'        => $u->id,
                'username'  => $u->username ?? null,
                'fullName'  => $u->full_name ?? null,
                'role'      => $u->role ?? 'OWNER',
                'email'     => $u->email ?? null,
                'contact'   => $u->phone_number ?? null,
                'branch'    => $u->branch ?? 'Chikin Tayo – QC Main',
                'accountId' => 'kk' . str_pad($u->id, 5, '0', STR_PAD_LEFT),
                'avatarUrl' => $avatarUrl,
            ],
        ]);
    }

    public function updateOwnerProfile(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);
        if (! $user) {
            return response()->json([
                'ok'      => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $userId = $user->id;

        $validated = $request->validate([
            'fullName' => 'nullable|string|max:255',
            'username' => ['nullable', 'string', 'max:255', Rule::unique('users', 'username')->ignore($userId)],
            'email'    => 'nullable|email|max:255',
            'contact'  => 'nullable|string|max:20',
            'password' => [
                'nullable',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}$/',
                'confirmed',
            ],
            'password_confirmation' => 'nullable|string|min:8',
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (!@#$%^&*).',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        $updateData = [
            'full_name' => $validated['fullName'] ?? $user->full_name,
            'email'     => $validated['email'] ?? $user->email,
            'phone_number'     => $validated['contact'] ?? $user->phone_number,
        ];

        // Update username if provided
        if (!empty($validated['username'])) {
            $updateData['username'] = $validated['username'];
        }

        // Update password if provided (already validated with confirmation)
        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
            $updateData['must_change_password'] = false;
        }

        DB::table('users')
            ->where('id', $userId)
            ->update($updateData);

        // Fetch and return updated user data
        $updatedUser = User::find($userId);

        // If password was updated and the account is a supplier (or bsupplier username),
        // auto-verify the email because procurement panel already sent credentials.
        if (!empty($validated['password']) && !empty($updatedUser->email) && is_null($updatedUser->email_verified_at)) {
            $roleUpper = strtoupper(trim($updatedUser->role ?? ''));
            $isBsupplierUser = isset($updatedUser->username) && Str::startsWith(strtolower($updatedUser->username), 'bsupplier');
            if ($roleUpper === 'SUPPLIER' || $isBsupplierUser) {
                try {
                    $updatedUser->email_verified_at = now();
                    $updatedUser->save();
                    Cache::forget('verification_code_' . $updatedUser->email);
                    Log::info('Auto-verified supplier email after profile password update for user id ' . $updatedUser->id);
                } catch (\Exception $e) {
                    Log::error('Failed to auto-verify supplier email after profile update: ' . $e->getMessage());
                }
            }
        }

        // Generate full absolute URL for avatar if it exists
        $avatarUrl = null;
        if ($updatedUser->avatar_url) {
            if (strpos($updatedUser->avatar_url, 'http') === 0) {
                $avatarUrl = $updatedUser->avatar_url;
            } else {
                $avatarUrl = url($updatedUser->avatar_url);
            }
        }

        return response()->json([
            'ok'      => true,
            'message' => 'Profile updated successfully',
            'user'    => [
                'id'        => $updatedUser->id,
                'username'  => $updatedUser->username ?? null,
                'fullName'  => $updatedUser->full_name ?? null,
                'role'      => $updatedUser->role ?? 'OWNER',
                'email'     => $updatedUser->email ?? null,
                'contact'   => $updatedUser->phone_number ?? null,
                'branch'    => $updatedUser->branch ?? 'Chikin Tayo – QC Main',
                'accountId' => 'kk' . str_pad($updatedUser->id, 5, '0', STR_PAD_LEFT),
                'avatarUrl' => $avatarUrl,
            ],
        ]);
    }

    public function uploadAvatar(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);
        if (! $user) {
            return response()->json([
                'ok'      => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        // Relax validation slightly (allow webp, larger files) and log request for debugging
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // max 5MB
        ]);

        $file = $request->file('avatar');

        if (! $file) {
            Log::debug('uploadAvatar: no file present in request', ['user_id' => $user->id ?? null]);
            return response()->json(['ok' => false, 'message' => 'No file uploaded'], 400);
        }

        try {
            // Generate a unique filename
            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();

            // Store in public/storage/avatars
            $path = $file->storeAs('avatars', $filename, 'public');

            // Update user avatar_url
            $storePath = '/storage/' . $path;
            DB::table('users')
                ->where('id', $user->id)
                ->update(['avatar_url' => $storePath]);

            Log::debug('uploadAvatar: stored avatar', ['user_id' => $user->id, 'path' => $path]);

            return response()->json([
                'ok'        => true,
                'avatarUrl' => url($storePath),
            ]);
        } catch (\Exception $ex) {
            Log::error('uploadAvatar error', ['user_id' => $user->id ?? null, 'exception' => $ex->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'Failed to upload avatar'], 500);
        }
    }

    // Public Authentication Methods for Comments System
    public function sendVerification(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = $request->input('email');

        // Rate limiting: max 3 requests per 10 minutes per email
        $rateLimitKey = 'verification_rate_limit_' . $email;
        $requestCount = Cache::get($rateLimitKey, 0);

        if ($requestCount >= 3) {
            return response()->json([
                'message' => 'Too many verification requests. Please try again in 10 minutes.'
            ], 429);
        }

        if (User::where('email', '=', $email)->exists()) {
            return response()->json([
                'message' => 'Email already exists. Please sign in instead.'
            ], 409);
        }

        // Generate 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store code in cache for 10 minutes
        Cache::put('verification_code_' . $email, $code, 600);

        // Increment rate limit counter
        Cache::put($rateLimitKey, $requestCount + 1, 600);

        // Log verification code for debugging (never expose to client)
        Log::info("Verification code generated for {$email}: {$code}");

        // Send email with verification code
        try {
            Mail::raw(
                "Your CHIKIN TAYO verification code is: {$code}\n\nThis code will expire in 10 minutes.",
                function ($message) use ($email) {
                    $message->to($email)
                            ->subject('CHIKIN TAYO - Email Verification Code');
                }
            );

            Log::info("Verification email sent to {$email}");

            // Code is NOT returned in response - sent only via email
            return response()->json([
                'message' => 'Verification code sent to your email',
                'email' => $email
            ]);
        } catch (\Exception $e) {
            // Log the error
            Log::error("Failed to send verification email to {$email}: " . $e->getMessage());

            // Do NOT expose the code - just indicate email failure
            return response()->json([
                'message' => 'Failed to send verification email. Please try again later.',
                'email' => $email,
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    private function resolveAuthenticatedUser($request)
    {
        if (Auth::check()) {
            return Auth::user();
        }

        $sessionUserId = $request->session()->get('user_id');
        if ($sessionUserId) {
            return User::find($sessionUserId);
        }

        return null;
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $email = $request->input('email');
        $code = $request->input('code');

        // Get stored code
        $storedCode = Cache::get('verification_code_' . $email);

        if (!$storedCode) {
            return response()->json([
                'message' => 'Verification code expired. Please request a new one.'
            ], 400);
        }

        if ($storedCode !== $code) {
            return response()->json([
                'message' => 'Invalid verification code.'
            ], 400);
        }

        // Code is valid - check if user exists
        $userExists = User::where('email', '=', $email)->exists();

        // Mark email as verified
        Cache::put('email_verified_' . $email, true, 3600);

        return response()->json([
            'message' => 'Email verified successfully',
            'user_exists' => $userExists
        ]);
    }

    /**
     * Confirm verification for an authenticated user and attach the verified email
     * POST /api/auth/confirm-email (requires auth middleware)
     */
    public function confirmEmail(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $email = $request->input('email');
        $code = $request->input('code');

        $storedCode = Cache::get('verification_code_' . $email);
        if (! $storedCode) {
            return response()->json(['message' => 'Verification code expired. Please request a new one.'], 400);
        }

        if ($storedCode !== $code) {
            return response()->json(['message' => 'Invalid verification code.'], 400);
        }

        // Ensure email isn't used by another user
        $existing = User::where('email', $email)->where('id', '!=', $user->id)->exists();
        if ($existing) {
            return response()->json(['message' => 'Email already in use by another account.'], 409);
        }

        // Update authenticated user's email and mark verified
        $user->email = $email;
        $user->email_verified_at = now();
        $user->save();

        // Clear caches
        Cache::forget('verification_code_' . $email);
        Cache::forget('email_verified_' . $email);

        return response()->json(['message' => 'Email associated and verified successfully', 'email' => $email]);
    }

    public function registerPublic(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'unique:users,email'],
            'username' => ['required', 'string', 'min:3', 'max:50', 'unique:users,username'],
            'password' => ['required', 'string', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'],
            'verification_code' => ['required', 'string', 'size:6'],
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number.',
            'password.min' => 'Password must be at least 8 characters long.'
        ]);

        $email = $request->input('email');

        // Check if email was verified
        if (!Cache::get('email_verified_' . $email)) {
            return response()->json([
                'message' => 'Email not verified. Please verify your email first.'
            ], 400);
        }

        // Create user
        $user = User::create([
            'email' => $email,
            'username' => $request->input('username'),
            'password' => $request->input('password'), // Model's setter will hash it
            'email_verified_at' => now(),
            'role' => 'customer', // default role for public registration
            'must_change_password' => true,
        ]);

        // Create customer account record
        CustomerAccount::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'full_name' => $request->input('username'), // Use username as initial full_name
            'status' => 'active',
            'last_activity_at' => now(),
        ]);

        // Clear verification cache
        Cache::forget('email_verified_' . $email);
        Cache::forget('verification_code_' . $email);

        // Create token
        $token = Str::random(60);
        Cache::put('user_token_' . $token, $user->id, 86400 * 30); // 30 days

        return response()->json([
            'message' => 'Account created successfully',
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'username' => $user->username,
            ],
            'token' => $token
        ], 201);
    }

    public function loginPublic(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('username', '=', $request->input('username'))
                    ->orWhere('email', '=', $request->input('username'))
                    ->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Create token
        $token = Str::random(60);
        Cache::put('user_token_' . $token, $user->id, 86400 * 30); // 30 days

        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'username' => $user->username,
                'must_change_password' => (bool) $user->must_change_password,
            ],
            'token' => $token
        ]);
    }
}
