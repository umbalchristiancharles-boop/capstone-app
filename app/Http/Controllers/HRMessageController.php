<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
                'attachment_url' => $m->attachment_path ? Storage::disk('public')->url($m->attachment_path) : null,
                'attachment_name' => $m->attachment_name,
                'attachment_mime' => $m->attachment_mime,
                'from_user_id' => $m->from_user_id,
                'from_user' => $m->fromUser ? ['id' => $m->fromUser->id, 'name' => $m->fromUser->full_name ?? $m->fromUser->username, 'role' => $m->fromUser->role ?? null] : null,
                'to_user_id' => $m->to_user_id,
                'to_user' => $m->toUser ? ['id' => $m->toUser->id, 'name' => $m->toUser->full_name ?? $m->toUser->username, 'role' => $m->toUser->role ?? null] : null,
                'delivered_at' => $m->delivered_at,
                'read_at' => $m->read_at,
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
        $unreadByUser = Message::where('to_user_id', $user->id)
            ->whereNull('read_at')
            ->selectRaw('from_user_id, COUNT(*) as unread_count')
            ->groupBy('from_user_id')
            ->pluck('unread_count', 'from_user_id');
        $users = $users->map(function ($chatUser) use ($unreadByUser) {
            $chatUser->unread_count = (int) ($unreadByUser[$chatUser->id] ?? 0);
            return $chatUser;
        });
        $unreadCount = Message::where('to_user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'users' => $users,
            'unread_count' => $unreadCount,
        ]);
    }

    public function send(Request $request)
    {
        $request->validate([
            'to_user_id' => 'required|integer|exists:users,id',
            'body' => 'nullable|string|max:5000',
            'attachment' => 'nullable|file|max:20480|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,txt',
        ]);

        if (! $request->filled('body') && ! $request->hasFile('attachment')) {
            return response()->json(['error' => 'Message or attachment is required'], 422);
        }

        $me = $this->currentUser();
        if (! $me) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
        
        // Prevent messaging self
        if ((int)$request->to_user_id === (int)$me->id) {
            return response()->json(['error' => 'Cannot message yourself'], 400);
        }
        
        $to = User::findOrFail($request->to_user_id);

        // Strict authorization check
        if (! $this->canChatWith($me, $to)) {
            return response()->json(['error' => 'Not authorized to message this user'], 403);
        }

        $attachment = $request->file('attachment');
        $msg = Message::create([
            'branch_id' => $me->branch_id ?? null,
            'from_user_id' => $me->id,
            'to_user_id' => $to->id,
            'body' => trim((string) $request->body),
            'attachment_path' => $attachment ? $attachment->store('message-attachments', 'public') : null,
            'attachment_name' => $attachment?->getClientOriginalName(),
            'attachment_mime' => $attachment?->getMimeType(),
            'delivered_at' => now(),
        ]);

        return response()->json(['message' => $msg]);
    }

    public function sendEmployeeReport(Request $request)
    {
        $request->validate([
            'to_user_id' => 'required|integer|exists:users,id',
            'report_body' => 'required|string|min:1|max:4500',
        ]);

        $me = $this->currentUser();
        if (! $me) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $to = User::findOrFail($request->to_user_id);

        if (! $this->canSubmitEmployeeReport($me) || (int) $to->id === (int) $me->id || ! $this->isHrManager($to)) {
            return response()->json(['error' => 'Employee reports can only be sent to an HR manager'], 403);
        }

        if (! $this->canChatWith($me, $to)) {
            return response()->json(['error' => 'Not authorized to message this user'], 403);
        }

        $employeeName = $me->full_name ?: $me->username ?: 'User #' . $me->id;
        $body = "EMPLOYEE REPORT\nEmployee: " . $employeeName . "\n\n" . trim($request->report_body);

        $msg = Message::create([
            'branch_id' => $me->branch_id ?? null,
            'from_user_id' => $me->id,
            'to_user_id' => $to->id,
            'body' => $body,
        ]);

        return response()->json(['message' => $msg]);
    }

    private function resolveChatUsersFor(User $user)
    {
        $meRole = strtoupper($user->role ?? '');
        $adminRoles = ['SUPER_ADMIN', 'ADMIN', 'OWNER'];
        $meBranch = $user->branch_id ?? null;
        
        // Check if user is in main branch
        $isMainBranchUser = false;
        if ($meBranch) {
            $myBranch = Branch::find($meBranch);
            $isMainBranchUser = $myBranch && $myBranch->is_main_branch;
        }

        // Owner, Admin, Super Admin, or main branch users: show all users except self
        if (in_array($meRole, $adminRoles) || $isMainBranchUser) {
            $users = User::where('id', '!=', $user->id)
                ->selectRaw("id, COALESCE(full_name, username, CONCAT('User #', id)) as name, role, department, branch_id")
                ->orderBy('name')
                ->get();
            return $users;
        }

        // CUSTOM or other branch users: show only users in their branch except self
        if ($meBranch) {
            $users = User::where('id', '!=', $user->id)
                ->where('branch_id', $meBranch)
                ->selectRaw("id, COALESCE(full_name, username, CONCAT('User #', id)) as name, role, department, branch_id")
                ->orderBy('name')
                ->get();
            return $users;
        }

        // Fallback for users with no branch assigned: return nothing
        return collect();
    }

    private function canChatWith(User $me, User $other): bool
    {
        $meRole = strtoupper($me->role ?? '');
        $otherRole = strtoupper($other->role ?? '');
        $adminRoles = ['SUPER_ADMIN', 'ADMIN', 'OWNER'];
        
        // Owner, Admin, Super Admin can message anyone
        if (in_array($meRole, $adminRoles) || in_array($otherRole, $adminRoles)) {
            return true;
        }

        $meBranch = $me->branch_id ?? null;
        $otherBranch = $other->branch_id ?? null;

        // Check if current user is in main branch
        if ($meBranch && $otherBranch) {
            $myBranch = Branch::find($meBranch);
            if ($myBranch && $myBranch->is_main_branch) {
                // Main branch users can message anyone
                return true;
            }

            // Check if other user is in main branch (if so, they can be messaged from anywhere)
            $otherUserBranch = Branch::find($otherBranch);
            if ($otherUserBranch && $otherUserBranch->is_main_branch) {
                return true;
            }
        }

        // Both must be in the same branch (handles CUSTOM, STAFF, MANAGER, etc. uniformly)
        return (int)$meBranch === (int)$otherBranch && $meBranch !== null;
    }

    private function isHrManager(User $user): bool
    {
        return strtoupper(trim($user->role ?? '')) === 'MANAGER'
            && strtoupper(trim($user->department ?? '')) === 'HR';
    }

    private function canSubmitEmployeeReport(User $user): bool
    {
        $role = strtoupper(trim($user->role ?? ''));

        return ! in_array($role, ['OWNER', 'ADMIN', 'SUPER_ADMIN', 'SUPERADMIN'], true)
            && ! $this->isHrManager($user);
    }

    private function currentUser(): User
    {
        return Auth::guard('sanctum')->user() ?? Auth::user();
    }
}
