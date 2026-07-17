<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CustomerReportController extends Controller
{
    /** Submit a new customer report/contact request */
    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $customerAccountId = null;
        
        // If user is authenticated as customer, link to their account
        if (Auth::check() && Auth::user()->role === 'CUSTOMER') {
            $customerAccount = Auth::user()->customerAccount;
            $customerAccountId = $customerAccount?->id;
        }

        $report = CustomerReport::create([
            'customer_account_id' => $customerAccountId,
            'customer_name' => $request->customer_name,
            'customer_email' => $request->customer_email,
            'customer_phone' => $request->customer_phone,
            'subject' => $request->subject,
            'message' => $request->message,
            'status' => 'pending',
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Your message has been sent to the admin. We will get back to you soon.',
            'report' => $report,
        ], 201);
    }

    /** Get all customer reports (Admin, Owner, SuperAdmin, HR, and Main Branch CRM) */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if user has authorized role
        $hasAuthorizedRole = in_array($user->role, ['ADMIN', 'OWNER', 'SUPERADMIN', 'HR']);
        
        // Check if user is from main branch (for CRM access)
        $isMainBranchUser = false;
        if ($user->branch_id) {
            $branch = \App\Models\Branch::find($user->branch_id);
            $isMainBranchUser = $branch && ($branch->is_main_branch || (int) $branch->id === 1);
        }

        if (!$hasAuthorizedRole && !$isMainBranchUser) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = CustomerReport::with(['customerAccount', 'assignedTo']);

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search by customer name, email, subject, or message
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $reports = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'ok' => true,
            'reports' => $reports,
        ]);
    }

    /** Get a single customer report */
    public function show($id)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $hasAuthorizedRole = in_array($user->role, ['ADMIN', 'OWNER', 'SUPERADMIN', 'HR']);
        $isMainBranchUser = false;
        if ($user->branch_id) {
            $branch = \App\Models\Branch::find($user->branch_id);
            $isMainBranchUser = $branch && ($branch->is_main_branch || (int) $branch->id === 1);
        }

        if (!$hasAuthorizedRole && !$isMainBranchUser) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $report = CustomerReport::with(['customerAccount', 'assignedTo'])->findOrFail($id);

        return response()->json([
            'ok' => true,
            'report' => $report,
        ]);
    }

    /** Update customer report status and admin notes */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $hasAuthorizedRole = in_array($user->role, ['ADMIN', 'OWNER', 'SUPERADMIN', 'HR']);
        $isMainBranchUser = false;
        if ($user->branch_id) {
            $branch = \App\Models\Branch::find($user->branch_id);
            $isMainBranchUser = $branch && ($branch->is_main_branch || (int) $branch->id === 1);
        }

        if (!$hasAuthorizedRole && !$isMainBranchUser) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $report = CustomerReport::findOrFail($id);

        $request->validate([
            'status' => 'nullable|in:pending,in_progress,resolved,closed',
            'admin_notes' => 'nullable|string|max:5000',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if ($request->has('status')) {
            $report->status = $request->status;
            
            // Set resolved_at when status changes to resolved
            if ($request->status === 'resolved' && !$report->resolved_at) {
                $report->resolved_at = now();
            }
        }

        if ($request->has('admin_notes')) {
            $report->admin_notes = $request->admin_notes;
        }

        if ($request->has('assigned_to')) {
            $report->assigned_to = $request->assigned_to;
        }

        $report->save();

        return response()->json([
            'ok' => true,
            'message' => 'Report updated successfully',
            'report' => $report->load(['customerAccount', 'assignedTo']),
        ]);
    }

    /** Delete a customer report */
    public function destroy($id)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $hasAuthorizedRole = in_array($user->role, ['ADMIN', 'OWNER', 'SUPERADMIN']);
        $isMainBranchUser = false;
        if ($user->branch_id) {
            $branch = \App\Models\Branch::find($user->branch_id);
            $isMainBranchUser = $branch && ($branch->is_main_branch || (int) $branch->id === 1);
        }

        if (!$hasAuthorizedRole && !$isMainBranchUser) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $report = CustomerReport::findOrFail($id);
        $report->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Report deleted successfully',
        ]);
    }

    /** Send email to customer who reported */
    public function sendEmail(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $hasAuthorizedRole = in_array($user->role, ['ADMIN', 'OWNER', 'SUPERADMIN', 'HR']);
        $isMainBranchUser = false;
        if ($user->branch_id) {
            $branch = \App\Models\Branch::find($user->branch_id);
            $isMainBranchUser = $branch && ($branch->is_main_branch || (int) $branch->id === 1);
        }

        if (!$hasAuthorizedRole && !$isMainBranchUser) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $report = CustomerReport::findOrFail($id);

        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        // Check if customer email exists
        if (!$report->customer_email) {
            return response()->json([
                'ok' => false,
                'message' => 'Customer email is not available for this report.',
            ], 400);
        }

        try {
            $emailSubject = $request->subject;
            $emailBody = $request->message;
            
            // Send email using the helper function
            send_raw_mail_notification($report->customer_email, $emailSubject, $emailBody);

            return response()->json([
                'ok' => true,
                'message' => 'Email sent successfully to ' . $report->customer_email,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to send email to customer', [
                'error' => $e->getMessage(),
                'report_id' => $report->id,
                'customer_email' => $report->customer_email,
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Failed to send email. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /** Get report statistics for dashboard */
    public function stats()
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $hasAuthorizedRole = in_array($user->role, ['ADMIN', 'OWNER', 'SUPERADMIN', 'HR']);
        $isMainBranchUser = false;
        if ($user->branch_id) {
            $branch = \App\Models\Branch::find($user->branch_id);
            $isMainBranchUser = $branch && ($branch->is_main_branch || (int) $branch->id === 1);
        }

        if (!$hasAuthorizedRole && !$isMainBranchUser) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $stats = [
            'total' => CustomerReport::count(),
            'pending' => CustomerReport::where('status', 'pending')->count(),
            'in_progress' => CustomerReport::where('status', 'in_progress')->count(),
            'resolved' => CustomerReport::where('status', 'resolved')->count(),
            'closed' => CustomerReport::where('status', 'closed')->count(),
        ];

        return response()->json([
            'ok' => true,
            'stats' => $stats,
        ]);
    }
}
