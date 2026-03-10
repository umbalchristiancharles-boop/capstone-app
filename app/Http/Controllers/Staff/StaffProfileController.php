<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class StaffProfileController extends Controller
{
    /**
     * Get the authenticated user (works for all staff roles)
     */
    private function getAuthenticatedUser(Request $request)
    {
        // Use auth() helper - works with 'auth' middleware (web guard)
        if (Auth::check()) {
            return Auth::user();
        }

        // Fallback: try to get user from session
        $userId = $request->session()->get('user_id');
        if ($userId) {
            return User::find($userId);
        }

        return null;
    }

    /**
     * Generate full absolute URL for avatar if it exists
     */
    private function getAvatarUrl($user)
    {
        if (!$user || !$user->avatar_url) {
            return null;
        }
        if (strpos($user->avatar_url, 'http') === 0) {
            return $user->avatar_url;
        }
        return url($user->avatar_url);
    }

    /**
     * Get authenticated staff profile
     * GET /api/staff/profile
     */
    public function profile(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);

        if (!$user) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        // Get branch name if branch_id exists
        $branchName = null;
        if ($user->branch_id) {
            $branch = \App\Models\Branch::find($user->branch_id);
            if ($branch) {
                $branchName = $branch->name;
            }
        }

        return response()->json([
            'ok' => true,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'contact' => $user->phone_number,
                'role' => $user->role,
                'department' => $user->department,
                'branch_id' => $user->branch_id,
                'branch_name' => $branchName,
                'avatarUrl' => $this->getAvatarUrl($user),
                'must_change_password' => (bool) $user->must_change_password,
            ]
        ]);
    }

    /**
     * Update authenticated staff profile
     * PUT /api/staff/profile
     */
    public function updateProfile(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);

        if (!$user) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $userId = $user->id;

        // Check if user is staff (not owner/admin/manager)
        $isStaff = ($user->role === 'staff');

        $validated = $request->validate([
            'full_name' => $isStaff ? 'nullable' : 'nullable|string|max:255',
            'username' => $isStaff ? 'nullable' : ['nullable', 'string', 'max:255', Rule::unique('users', 'username')->ignore($userId)],
            'email' => $isStaff ? 'nullable' : 'nullable|email|max:255',
            'phone_number' => $isStaff ? 'nullable' : 'nullable|string|max:20',
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

        // For staff role: ONLY allow password update, block all other fields
        if ($isStaff) {
            $updateData = [];

            // Only update password if provided (already validated with confirmation)
            if (!empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
                $updateData['must_change_password'] = false;
            }

            if (!empty($updateData)) {
                DB::table('users')
                    ->where('id', $userId)
                    ->update($updateData);
            }

            // Fetch and return user data
            $updatedUser = User::find($userId);

            return response()->json([
                'ok' => true,
                'message' => 'Password updated successfully',
                'user' => [
                    'id' => $updatedUser->id,
                    'username' => $updatedUser->username,
                    'full_name' => $updatedUser->full_name,
                    'email' => $updatedUser->email,
                    'phone_number' => $updatedUser->phone_number,
                    'role' => $updatedUser->role,
                    'department' => $updatedUser->department,
                    'avatarUrl' => $this->getAvatarUrl($updatedUser),
                ]
            ]);
        }

        // For owner/admin/manager: allow all fields to be updated
        $updateData = [
            'full_name' => $validated['full_name'] ?? $user->full_name,
            'email' => $validated['email'] ?? $user->email,
            'phone_number' => $validated['phone_number'] ?? $user->phone_number,
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

        return response()->json([
            'ok' => true,
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $updatedUser->id,
                'username' => $updatedUser->username,
                'full_name' => $updatedUser->full_name,
                'email' => $updatedUser->email,
                'phone_number' => $updatedUser->phone_number,
                'role' => $updatedUser->role,
                'department' => $updatedUser->department,
                'avatarUrl' => $this->getAvatarUrl($updatedUser),
            ]
        ]);
    }

    /**
     * Upload avatar for authenticated staff
     * POST /api/staff/avatar
     */
    public function uploadAvatar(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);

        if (!$user) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        // Validate the uploaded file
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // max 5MB
        ]);

        $file = $request->file('avatar');

        if (!$file) {
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

            Log::debug('Staff uploadAvatar: stored avatar', ['user_id' => $user->id, 'path' => $path]);

            return response()->json([
                'ok' => true,
                'avatarUrl' => url($storePath),
            ]);
        } catch (\Exception $ex) {
            Log::error('Staff uploadAvatar error', ['user_id' => $user->id ?? null, 'exception' => $ex->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'Failed to upload avatar'], 500);
        }
    }
}
