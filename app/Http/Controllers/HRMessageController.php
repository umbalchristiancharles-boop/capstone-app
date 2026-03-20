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

        $users = $this->resolveChatUsersFor($user);

        return view('hr.messages', compact('users'));
    }

    public function conversation($otherUserId)
    {
        $me = $this->currentUser();
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
                'to_user' => $m->toUser ? ['id' => $m->toUser->id, 'name' => $m->toUser->full_name ?? $m->toUser->username] : null,
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
        $role = strtoupper($user->role ?? '');

        // For HR users: surface people in their branch plus anyone they already have messages with.
        if ($this->isHrRole($role)) {
            $partnerIds = Message::where(function ($q) use ($user) {
                $q->where('from_user_id', $user->id)->orWhere('to_user_id', $user->id);
            })->pluck('from_user_id', 'to_user_id')->flatten()->unique()->reject(fn ($id) => (int) $id === (int) $user->id);

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

        // For staff/manager: return HR contacts (prefer same branch or global HR).
        $hrQuery = User::query()->whereRaw("UPPER(role) LIKE '%HR%'");

        if ($user->branch_id) {
            $hrQuery->where(function ($q) use ($user) {
                $q->whereNull('branch_id')->orWhere('branch_id', $user->branch_id);
            });
        }

        $hrUsers = $hrQuery->selectRaw("id, COALESCE(full_name, username, CONCAT('User #', id)) as name, role, branch_id")
            ->orderBy('name')->get();

        if ($hrUsers->isNotEmpty()) {
            return $hrUsers;
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
        $meRole = strtoupper($me->role ?? '');
        $otherRole = strtoupper($other->role ?? '');

        if ($this->isHrRole($meRole) || $this->isHrRole($otherRole)) {
            return true;
        }

        return ($me->branch_id ?? null) === ($other->branch_id ?? null);
    }

    private function isHrRole(string $role): bool
    {
        return $role === 'HR' || str_contains($role, 'HR');
    }

    private function currentUser(): User
    {
        return Auth::guard('sanctum')->user() ?? Auth::user();
    }
}
