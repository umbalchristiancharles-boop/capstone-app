<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HRMessageController extends Controller
{
    public function index()
    {
        $user = $this->currentUser();

        if (! $user) {
            return redirect()->route('login');
        }

        $users = $this->resolveChatUsersFor($user);

        return view('hr.messages', compact('users'));
    }

    public function conversation($otherUserId)
    {
        $me = $this->currentUser();
        if (! $me) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
        $other = User::findOrFail($otherUserId);

        if (! $this->canChatWith($me, $other)) {
            return response()->json(['error' => 'User not in same branch'], 403);
        }

        $messages = Message::with(['fromUser:id,full_name,username,role','toUser:id,full_name,username'])
            ->where(function ($q) use ($me, $other) {
                $q->where('from_user_id', $me->id)->where('to_user_id', $other->id);
            })->orWhere(function ($q) use ($me, $other) {
                $q->where('from_user_id', $other->id)->where('to_user_id', $me->id);
            })->orderBy('created_at', 'asc')->get();

        // map messages to include sender/recipient summary to avoid lazy-loading in frontend
        $messages = $messages->map(function ($m) {
            return [
                'id' => $m->id,
                'body' => $m->body,
                'from_user_id' => $m->from_user_id,
                'from_user' => $m->fromUser ? ['id' => $m->fromUser->id, 'name' => $m->fromUser->full_name ?? $m->fromUser->username, 'role' => $m->fromUser->role ?? null] : null,
                'to_user_id' => $m->to_user_id,
                'to_user' => $m->toUser ? ['id' => $m->toUser->id, 'name' => $m->toUser->full_name ?? $m->toUser->username, 'role' => $m->toUser->role ?? null] : null,
                'created_at' => $m->created_at,
            ];
        });

        // mark unread messages to me as read
        Message::where('to_user_id', $me->id)->where('from_user_id', $other->id)->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['messages' => $messages]);
    }

    public function users()
    {
        $user = $this->currentUser();
        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $users = $this->resolveChatUsersFor($user);

        return response()->json(['users' => $users]);
    }

    public function send(Request $request)
    {
        $request->validate([
            'to_user_id' => 'required|integer|exists:users,id',
            'body' => 'required|string',
        ]);

        $me = $this->currentUser();
        if (! $me) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
        $to = User::findOrFail($request->to_user_id);

        if (! $this->canChatWith($me, $to)) {
            return response()->json(['error' => 'User not in same branch'], 403);
        }

        $msg = Message::create([
            'branch_id' => $me->branch_id ?? null,
            'from_user_id' => $me->id,
            'to_user_id' => $to->id,
            'body' => $request->body,
        ]);

        return response()->json(['message' => $msg]);
    }

    private function resolveChatUsersFor(User $user)
    {
        // For HR users: surface people in their branch plus anyone they already have messages with.
        if ($this->isHrUser($user)) {
            $partnerIds = Message::where('from_user_id', $user->id)
                ->orWhere('to_user_id', $user->id)
                ->get(['from_user_id', 'to_user_id'])
                ->flatMap(function ($m) {
                    return [$m->from_user_id, $m->to_user_id];
                })
                ->filter()
                ->unique()
                ->reject(fn ($id) => (int) $id === (int) $user->id)
                ->values();

            $query = User::query()->where('id', '!=', $user->id);

            if ($user->branch_id) {
                $query->where(function ($q) use ($user, $partnerIds) {
                    $q->where('branch_id', $user->branch_id);
                    if ($partnerIds->isNotEmpty()) {
                        $q->orWhereIn('id', $partnerIds);
                    }
                });
            } elseif ($partnerIds->isNotEmpty()) {
                $query->whereIn('id', $partnerIds);
            } else {
                $query->whereIn('role', ['STAFF', 'MANAGER', 'BRANCH_MANAGER', 'BRANCH MANAGER']);
            }

            return $query->selectRaw("id, COALESCE(full_name, username, CONCAT('User #', id)) as name, role, branch_id")
                ->orderBy('name')->distinct()->get();
        }

        // For staff/manager: prefer HR in same branch; fallback to global HR.
        if ($user->branch_id) {
            $sameBranchHr = User::query()
                ->where(function ($q) {
                    $q->whereRaw("UPPER(role) LIKE '%HR%'")
                        ->orWhereRaw("UPPER(COALESCE(department, '')) = 'HR'");
                })
                ->where('branch_id', $user->branch_id)
                ->selectRaw("id, COALESCE(full_name, username, CONCAT('User #', id)) as name, role, branch_id")
                ->orderBy('name')
                ->get();

            // Also include CUSTOM accounts with HR module permission in same branch
            $customHrUsers = User::where('role', 'CUSTOM')
                ->where('branch_id', $user->branch_id)
                ->get(['id', 'full_name', 'username', 'role', 'branch_id', 'permissions']);
            
            foreach ($customHrUsers as $cu) {
                try {
                    $perms = $cu->permissions ?? [];
                    if (is_string($perms)) $perms = json_decode($perms, true) ?: [];
                    if (is_array($perms) && isset($perms['modules']) && is_array($perms['modules'])) {
                        foreach ($perms['modules'] as $m) {
                            if (strtoupper(trim((string)$m)) === 'HR') {
                                $sameBranchHr->push((object)[
                                    'id' => $cu->id,
                                    'name' => $cu->full_name ?? $cu->username ?? ('User #' . $cu->id),
                                    'role' => $cu->role,
                                    'branch_id' => $cu->branch_id,
                                ]);
                                break;
                            }
                        }
                    }
                } catch (\Throwable $e) { /* ignore */ }
            }

            if ($sameBranchHr->isNotEmpty()) {
                return $sameBranchHr->sortBy('name')->values();
            }
        }

        $globalHr = User::query()
            ->where(function ($q) {
                $q->whereRaw("UPPER(role) LIKE '%HR%'")
                    ->orWhereRaw("UPPER(COALESCE(department, '')) = 'HR'");
            })
            ->whereNull('branch_id')
            ->selectRaw("id, COALESCE(full_name, username, CONCAT('User #', id)) as name, role, branch_id")
            ->orderBy('name')
            ->get();

        // Also include global CUSTOM accounts with HR module permission
        $customGlobalHrUsers = User::where('role', 'CUSTOM')
            ->whereNull('branch_id')
            ->get(['id', 'full_name', 'username', 'role', 'branch_id', 'permissions']);
        
        foreach ($customGlobalHrUsers as $cu) {
            try {
                $perms = $cu->permissions ?? [];
                if (is_string($perms)) $perms = json_decode($perms, true) ?: [];
                if (is_array($perms) && isset($perms['modules']) && is_array($perms['modules'])) {
                    foreach ($perms['modules'] as $m) {
                        if (strtoupper(trim((string)$m)) === 'HR') {
                            $globalHr->push((object)[
                                'id' => $cu->id,
                                'name' => $cu->full_name ?? $cu->username ?? ('User #' . $cu->id),
                                'role' => $cu->role,
                                'branch_id' => $cu->branch_id,
                            ]);
                            break;
                        }
                    }
                }
            } catch (\Throwable $e) { /* ignore */ }
        }

        if ($globalHr->isNotEmpty()) {
            return $globalHr->sortBy('name')->values();
        }

        // Fallback so the widget never shows an empty list: pick admins/owners in same branch.
        $fallback = User::where('id', '!=', $user->id)
            ->when($user->branch_id, function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            })
            ->whereIn('role', ['ADMIN', 'OWNER'])
            ->selectRaw("id, COALESCE(full_name, username, CONCAT('User #', id)) as name, role, branch_id")
            ->orderBy('name')
            ->get();

        return $fallback;
    }

    private function canChatWith(User $me, User $other): bool
    {
        // Allow admins/owners/super-admins to view any conversation
        $meRole = strtoupper($me->role ?? '');
        $otherRole = strtoupper($other->role ?? '');
        $adminRoles = ['SUPER_ADMIN', 'ADMIN', 'OWNER'];
        if (in_array($meRole, $adminRoles) || in_array($otherRole, $adminRoles)) {
            return true;
        }
        $meBranch = $me->branch_id ?? null;
        $otherBranch = $other->branch_id ?? null;

        if ($this->isHrUser($me) || $this->isHrUser($other)) {
            // HR chats are branch-scoped unless one side is global (no branch).
            if ($meBranch === null || $otherBranch === null) {
                return true;
            }

            return (int) $meBranch === (int) $otherBranch;
        }

        return $meBranch === $otherBranch;
    }

    private function isHrRole(string $role): bool
    {
        return $role === 'HR' || str_contains($role, 'HR');
    }

    private function isHrUser(User $user): bool
    {
        $role = strtoupper($user->role ?? '');
        $department = strtoupper($user->department ?? '');

        if ($this->isHrRole($role) || $department === 'HR') {
            return true;
        }

        // Check CUSTOM accounts with HR module permission
        if ($role === 'CUSTOM') {
            try {
                $perms = $user->permissions ?? [];
                if (is_string($perms)) $perms = json_decode($perms, true) ?: [];
                if (is_array($perms) && isset($perms['modules']) && is_array($perms['modules'])) {
                    foreach ($perms['modules'] as $m) {
                        if (strtoupper(trim((string)$m)) === 'HR') return true;
                    }
                }
            } catch (\Throwable $e) { /* ignore */ }
        }

        return false;
    }

    private function currentUser(): User
    {
        return Auth::guard('sanctum')->user() ?? Auth::user();
    }
}
