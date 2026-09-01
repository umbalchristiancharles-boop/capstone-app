<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\PositionOpenRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class BranchOpenPositionBroadcastTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('name')->nullable();
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('square_meters', 10, 2)->nullable();
            $table->decimal('geofencing_radius', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_main_branch')->default(false);
            $table->string('approval_status')->nullable()->default('approved');
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->unsignedBigInteger('finance_confirmed_by')->nullable();
            $table->timestamp('finance_confirmed_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->integer('budget')->nullable();
            $table->string('default_password')->nullable();
            $table->timestamp('default_password_updated_at')->nullable();
            $table->json('permit_bills')->nullable();
            $table->json('construction_costs')->nullable();
            $table->json('equipment_costs')->nullable();
            $table->decimal('total_investment', 12, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->nullable();
            $table->string('password');
            $table->string('full_name')->nullable();
            $table->string('role')->default('STAFF');
            $table->string('department')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('must_change_password')->default(false);
            $table->string('required_setup_type')->nullable();
            $table->json('permissions')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('department')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('position_open_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('position_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('requested_by_user_id');
            $table->integer('quantity')->default(1);
            $table->text('notes')->nullable();
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('position_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('position_open_request_id');
            $table->unsignedBigInteger('position_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('department')->nullable();
            $table->string('job_title');
            $table->string('applicant_full_name');
            $table->string('applicant_email');
            $table->string('applicant_phone')->nullable();
            $table->text('applicant_address')->nullable();
            $table->text('cover_letter')->nullable();
            $table->integer('years_of_experience')->default(0);
            $table->string('education')->nullable();
            $table->date('available_start_date')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('portfolio_url')->nullable();
            $table->boolean('privacy_consent')->default(false);
            $table->string('website')->nullable();
            $table->string('resume_path')->nullable();
            $table->json('supporting_documents_paths')->nullable();
            $table->string('status')->default('Submitted');
            $table->date('interview_date')->nullable();
            $table->time('interview_time')->nullable();
            $table->text('interview_notes')->nullable();
            $table->timestamps();
        });
    }

    public function test_branch_creation_broadcasts_open_positions_instead_of_creating_accounts_immediately(): void
    {
        $owner = User::create([
            'username' => 'owner_test',
            'email' => 'owner@example.com',
            'password' => 'Password123!',
            'full_name' => 'Owner User',
            'role' => 'OWNER',
            'is_active' => true,
            'must_change_password' => false,
        ]);

        $this->actingAs($owner);

        $response = $this->postJson('/api/superadmin/branches', [
            'code' => 'HB01',
            'name' => 'Harbor Branch',
            'address' => 'Harbor St',
            'budget' => 100000,
            'accounts' => [
                'admin' => true,
                'hr' => true,
                'finance' => true,
                'procurement' => true,
                'logistics' => true,
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('ok', true);

        $this->assertDatabaseCount('branches', 1);
        $this->assertDatabaseCount('position_open_requests', 5);
        $this->assertSame(0, User::where('branch_id', 1)->count());
        $this->assertSame('Approved', PositionOpenRequest::first()->status);
    }

    public function test_main_branch_hr_sees_all_branch_applications_but_branch_hr_only_sees_own_branch(): void
    {
        $mainBranch = Branch::create([
            'code' => 'HQ',
            'name' => 'Main Branch',
            'is_main_branch' => true,
            'is_active' => true,
            'approval_status' => 'approved',
            'budget' => 100000,
        ]);

        $branchA = Branch::create([
            'code' => 'BRA',
            'name' => 'Branch A',
            'is_main_branch' => false,
            'is_active' => true,
            'approval_status' => 'approved',
            'budget' => 100000,
        ]);

        $branchB = Branch::create([
            'code' => 'BRB',
            'name' => 'Branch B',
            'is_main_branch' => false,
            'is_active' => true,
            'approval_status' => 'approved',
            'budget' => 100000,
        ]);

        $mainHr = User::create([
            'username' => 'main_hr',
            'email' => 'mainhr@example.com',
            'password' => 'Password123!',
            'full_name' => 'Main HR',
            'role' => 'HR',
            'branch_id' => $mainBranch->id,
            'is_active' => true,
            'must_change_password' => false,
        ]);

        $branchHr = User::create([
            'username' => 'branch_hr',
            'email' => 'branchhr@example.com',
            'password' => 'Password123!',
            'full_name' => 'Branch HR',
            'role' => 'HR',
            'branch_id' => $branchA->id,
            'is_active' => true,
            'must_change_password' => false,
        ]);

        $positionA = \App\Models\Position::create([
            'name' => 'Cashier',
            'department' => 'FINANCE',
            'description' => 'Cashier role',
            'is_active' => true,
        ]);

        $positionB = \App\Models\Position::create([
            'name' => 'Cook',
            'department' => 'KITCHEN',
            'description' => 'Cook role',
            'is_active' => true,
        ]);

        $requestA = \App\Models\PositionOpenRequest::create([
            'position_id' => $positionA->id,
            'branch_id' => $branchA->id,
            'requested_by_user_id' => $branchHr->id,
            'quantity' => 1,
            'status' => 'Approved',
        ]);

        $requestB = \App\Models\PositionOpenRequest::create([
            'position_id' => $positionB->id,
            'branch_id' => $branchB->id,
            'requested_by_user_id' => $mainHr->id,
            'quantity' => 1,
            'status' => 'Approved',
        ]);

        \App\Models\PositionApplication::create([
            'position_open_request_id' => $requestA->id,
            'position_id' => $positionA->id,
            'branch_id' => $branchA->id,
            'department' => 'FINANCE',
            'job_title' => 'Cashier',
            'applicant_full_name' => 'Branch A Applicant',
            'applicant_email' => 'brancha@example.com',
            'applicant_phone' => '09123456789',
            'applicant_address' => 'Branch A address',
            'cover_letter' => 'Interested',
            'years_of_experience' => 2,
            'education' => 'College',
            'available_start_date' => '2026-10-01',
            'privacy_consent' => true,
            'status' => 'Submitted',
        ]);

        \App\Models\PositionApplication::create([
            'position_open_request_id' => $requestB->id,
            'position_id' => $positionB->id,
            'branch_id' => $branchB->id,
            'department' => 'KITCHEN',
            'job_title' => 'Cook',
            'applicant_full_name' => 'Branch B Applicant',
            'applicant_email' => 'branchb@example.com',
            'applicant_phone' => '09987654321',
            'applicant_address' => 'Branch B address',
            'cover_letter' => 'Interested',
            'years_of_experience' => 3,
            'education' => 'Senior High',
            'available_start_date' => '2026-10-01',
            'privacy_consent' => true,
            'status' => 'Submitted',
        ]);

        $this->actingAs($mainHr)
            ->getJson('/api/hr/positions/applications')
            ->assertOk()
            ->assertJsonCount(2, 'applications');

        $this->actingAs($branchHr)
            ->getJson('/api/hr/positions/applications')
            ->assertOk()
            ->assertJsonCount(1, 'applications')
            ->assertJsonPath('applications.0.branch_id', $branchA->id);
    }
}
