<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\PositionOpenRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
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
            $table->text('rejection_reason')->nullable();
            $table->decimal('budget', 15, 2)->nullable();
            $table->integer('initial_budget')->nullable();
            $table->string('default_password')->nullable();
            $table->timestamp('default_password_updated_at')->nullable();
            $table->json('permit_bills')->nullable();
            $table->json('construction_costs')->nullable();
            $table->json('equipment_costs')->nullable();
            $table->json('cost_attachments')->nullable();
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
            $table->string('account_type')->default('standard');
            $table->json('account_config')->nullable();
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

    public function test_branch_creation_leaves_open_positions_to_hr_instead_of_broadcasting_them(): void
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
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('ok', true);

        $this->assertDatabaseCount('branches', 1);
        $this->assertDatabaseCount('position_open_requests', 0);
        $this->assertSame(0, User::where('branch_id', 1)->count());
    }

    public function test_main_branch_admin_branch_creation_does_not_create_open_positions(): void
    {
        $mainBranch = Branch::create([
            'code' => 'HQ',
            'name' => 'Main Branch',
            'is_main_branch' => true,
            'is_active' => true,
            'approval_status' => 'approved',
            'budget' => 300000,
        ]);

        $admin = User::create([
            'username' => 'main_admin',
            'email' => 'main-admin@example.com',
            'password' => 'Password123!',
            'full_name' => 'Main Admin',
            'role' => 'ADMIN',
            'branch_id' => $mainBranch->id,
            'is_active' => true,
            'must_change_password' => false,
        ]);

        $owner = User::create([
            'username' => 'owner_approval',
            'email' => 'owner-approval@example.com',
            'password' => 'Password123!',
            'full_name' => 'Owner Approval',
            'role' => 'OWNER',
            'is_active' => true,
            'must_change_password' => false,
        ]);

        $mainHr = User::create([
            'username' => 'main_hr_approval',
            'email' => 'main-hr-approval@example.com',
            'password' => 'Password123!',
            'full_name' => 'Main HR',
            'role' => 'MANAGER',
            'department' => 'HR',
            'branch_id' => $mainBranch->id,
            'is_active' => true,
            'must_change_password' => false,
        ]);

        $this->actingAs($admin)
            ->postJson('/api/superadmin/branches', [
                'code' => 'OW01',
                'name' => 'Owner Approval Branch',
                'budget' => 100000,
            ])
            ->assertCreated()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('message', "Branch 'Owner Approval Branch' created and awaiting Main Branch Finance invoice and budget confirmation.");

        $branch = Branch::where('code', 'OW01')->firstOrFail();
        $this->assertSame('pending_finance', $branch->approval_status);
        $this->assertFalse((bool) $branch->is_active);
        $this->assertSame(0, PositionOpenRequest::where('branch_id', $branch->id)->count());

        $this->actingAs($owner)
            ->postJson("/api/owner/branch-requests/{$branch->id}/approve")
            ->assertStatus(409)
            ->assertJsonPath('message', 'Branch must be confirmed by Main Branch Finance before Owner approval.');
        $this->assertNull($branch->fresh()->approved_by);
        $this->assertSame('pending_finance', $branch->fresh()->approval_status);
        $this->assertFalse((bool) $branch->fresh()->is_active);
        $this->getJson('/api/owner/branch-requests')
            ->assertOk()
            ->assertJsonCount(0, 'branches');

        $financeUser = User::create([
            'username' => 'main_finance_approval',
            'email' => 'main-finance-approval@example.com',
            'password' => 'Password123!',
            'full_name' => 'Main Finance',
            'role' => 'MANAGER',
            'department' => 'FINANCE',
            'branch_id' => $mainBranch->id,
            'is_active' => true,
            'must_change_password' => false,
        ]);

        $this->actingAs($financeUser)
            ->getJson('/api/main-branch/finance/branch-requests')
            ->assertOk()
            ->assertJsonPath('branches.0.id', $branch->id);

        $this->postJson("/api/main-branch/finance/branch-requests/{$branch->id}/approve", ['budget' => 100000])
            ->assertOk()
            ->assertJsonPath('ok', true);
        $this->assertSame('pending_owner', $branch->fresh()->approval_status);
        $this->assertSame(100000, (int) $branch->fresh()->budget);
        $this->assertSame(100000, (int) $branch->fresh()->initial_budget);
        $this->assertFalse((bool) $branch->fresh()->is_active);
        $this->assertSame(300000, (int) $mainBranch->fresh()->budget);

        $this->actingAs($owner)
            ->getJson('/api/owner/branch-requests')
            ->assertOk()
            ->assertJsonPath('branches.0.id', $branch->id);
        $this->postJson("/api/owner/branch-requests/{$branch->id}/approve")
            ->assertOk()
            ->assertJsonPath('ok', true);
        $this->assertSame($owner->id, $branch->fresh()->approved_by);
        $this->assertSame('approved', $branch->fresh()->approval_status);
        $this->assertTrue((bool) $branch->fresh()->is_active);
        $this->assertSame(200000, (int) $mainBranch->fresh()->budget);

        $this->actingAs($mainHr)
            ->getJson('/api/hr/positions/requests/pending')
            ->assertOk()
            ->assertJsonCount(0, 'requests');
        $this->getJson('/api/public/positions/approved')
            ->assertOk()
            ->assertJsonCount(0, 'approved_positions');

        $this->postJson("/api/owner/branch-requests/{$branch->id}/approve")
            ->assertStatus(409);

        $this->assertSame('approved', $branch->fresh()->approval_status);
        $this->assertTrue((bool) $branch->fresh()->is_active);

        $this->actingAs($mainHr)
            ->getJson('/api/hr/positions/requests/pending')
            ->assertOk()
            ->assertJsonCount(0, 'requests');
        $this->getJson('/api/public/positions/approved')
            ->assertOk()
            ->assertJsonCount(0, 'approved_positions');
    }

    public function test_branch_creation_does_not_save_budget_values_or_unattached_costs(): void
    {
        $owner = User::create([
            'username' => 'budget_owner',
            'email' => 'budget-owner@example.com',
            'password' => 'Password123!',
            'full_name' => 'Budget Owner',
            'role' => 'OWNER',
            'is_active' => true,
            'must_change_password' => false,
        ]);

        $this->actingAs($owner);

        $response = $this->postJson('/api/superadmin/branches', [
            'code' => 'BI01',
            'name' => 'Budget Branch',
            'budget' => 250000,
            'accounts' => [],
        ]);

        $response->assertStatus(201)->assertJsonPath('ok', true);

        $this->assertDatabaseHas('branches', [
            'code' => 'BI01',
            'budget' => null,
            'initial_budget' => null,
            'total_investment' => null,
        ]);
    }

    public function test_branch_creation_stores_multiple_cost_attachments_per_section(): void
    {
        Storage::fake('local');

        $owner = User::create([
            'username' => 'attachment_owner',
            'email' => 'attachment-owner@example.com',
            'password' => 'Password123!',
            'full_name' => 'Attachment Owner',
            'role' => 'OWNER',
            'is_active' => true,
            'must_change_password' => false,
        ]);

        $response = $this->actingAs($owner)->post('/api/superadmin/branches', [
            'code' => 'AT01',
            'name' => 'Attachment Branch',
            'budget' => 250000,
            'accounts' => [],
            'cost_attachments' => [
                'permit_bills' => [
                    UploadedFile::fake()->create('business-permit.pdf', 50, 'application/pdf'),
                    UploadedFile::fake()->create('permit-receipt.pdf', 50, 'application/pdf'),
                ],
                'construction_costs' => [
                    UploadedFile::fake()->create('construction-invoice.pdf', 50, 'application/pdf'),
                    UploadedFile::fake()->create('construction-receipt.pdf', 50, 'application/pdf'),
                ],
                'equipment_costs' => [
                    UploadedFile::fake()->create('equipment-invoice.pdf', 50, 'application/pdf'),
                    UploadedFile::fake()->create('equipment-receipt.pdf', 50, 'application/pdf'),
                ],
            ],
            'cost_attachment_amounts' => [
                'permit_bills' => [2500, 500],
                'construction_costs' => [30000, 5000],
                'equipment_costs' => [12000, 800],
            ],
        ]);

        $response->assertStatus(201)->assertJsonPath('ok', true);

        $branch = Branch::where('code', 'AT01')->firstOrFail();
        foreach (['permit_bills', 'construction_costs', 'equipment_costs'] as $category) {
            $this->assertCount(2, $branch->cost_attachments[$category]);
            foreach ($branch->cost_attachments[$category] as $attachment) {
                Storage::disk('local')->assertExists($attachment['path']);
                $this->assertArrayHasKey('amount', $attachment);
            }
        }
        $this->assertEquals(2500, $branch->cost_attachments['permit_bills'][0]['amount']);
        $this->assertEquals(500, $branch->cost_attachments['permit_bills'][1]['amount']);
        $this->assertSame(50800.0, (float) $branch->total_investment);

        $this->actingAs($owner)
            ->get("/api/superadmin/branches/{$branch->id}/cost-attachments/permit_bills/0")
            ->assertOk();
    }

    public function test_branch_cost_attachment_can_be_viewed_inline_when_preview_is_requested(): void
    {
        Storage::fake('local');

        $owner = User::create([
            'username' => 'preview_owner',
            'email' => 'preview-owner@example.com',
            'password' => 'Password123!',
            'full_name' => 'Preview Owner',
            'role' => 'OWNER',
            'is_active' => true,
            'must_change_password' => false,
        ]);

        $image = UploadedFile::fake()->image('business-permit.jpg');
        $path = $image->store('branch-cost-attachments', 'local');
        $branch = Branch::create([
            'code' => 'PV01',
            'name' => 'Preview Branch',
            'approval_status' => 'pending_finance',
            'cost_attachments' => [
                'permit_bills' => [[
                    'name' => 'business-permit.jpg',
                    'path' => $path,
                    'mime' => 'image/jpeg',
                ]],
            ],
        ]);

        $this->actingAs($owner)
            ->get("/api/superadmin/branches/{$branch->id}/cost-attachments/permit_bills/0?preview=1")
            ->assertOk()
            ->assertHeader('Content-Type', 'image/jpeg')
            ->assertHeader('Content-Disposition', 'inline')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_pending_finance_branch_requests_include_initial_budget(): void
    {
        $financeUser = User::create([
            'username' => 'finance_admin',
            'email' => 'finance-admin@example.com',
            'password' => 'Password123!',
            'full_name' => 'Finance Admin',
            'role' => 'SUPER_ADMIN',
            'is_active' => true,
            'must_change_password' => false,
        ]);

        $branch = Branch::create([
            'code' => 'FIN01',
            'name' => 'Finance Branch',
            'budget' => 100000,
            'initial_budget' => 250000,
            'approval_status' => 'pending_finance',
            'requested_by' => $financeUser->id,
        ]);

        $this->actingAs($financeUser)
            ->getJson('/api/main-branch/finance/branch-requests')
            ->assertOk()
            ->assertJsonPath('branches.0.budget', 100000)
            ->assertJsonPath('branches.0.initial_budget', 250000);
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
            ->assertJsonPath('applications.0.branch_id', $branchA->id)
            ->assertJsonPath('applications.0.branch_name', $branchA->name);
    }

    public function test_main_branch_hr_can_publish_multiple_custom_accounts_for_a_confirmed_branch(): void
    {
        $mainBranch = Branch::create([
            'code' => 'HQ',
            'name' => 'Main Branch',
            'is_main_branch' => true,
            'is_active' => true,
            'approval_status' => 'approved',
            'budget' => 100000,
        ]);

        $branch = Branch::create([
            'code' => 'NBR1',
            'name' => 'Newly Confirmed Branch',
            'is_active' => true,
            'is_main_branch' => false,
            'approval_status' => 'approved',
            'approved_at' => now(),
            'budget' => 100000,
        ]);

        $hr = User::create([
            'username' => 'main_hr_openings',
            'email' => 'main-hr-openings@example.com',
            'password' => 'Password123!',
            'full_name' => 'Main Branch HR',
            'role' => 'MANAGER',
            'department' => 'HR',
            'branch_id' => $mainBranch->id,
            'is_active' => true,
            'must_change_password' => false,
        ]);

        $position = \App\Models\Position::create([
            'name' => 'Custom Account',
            'department' => 'CUSTOM',
            'description' => 'Custom account with assigned module access',
            'is_active' => true,
        ]);
        $adminPosition = \App\Models\Position::create([
            'name' => 'Admin',
            'department' => 'ADMIN',
            'is_active' => true,
        ]);

        $this->actingAs($hr)
            ->getJson('/api/hr/positions/branches/awaiting-open-positions')
            ->assertOk()
            ->assertJsonCount(1, 'branches')
            ->assertJsonPath('branches.0.id', $branch->id)
            ->assertJsonPath('branches.0.covered_module_count', 0)
            ->assertJsonPath('branches.0.total_module_count', 20)
            ->assertJsonPath('branches.0.remaining_module_count', 20);

        $this->postJson("/api/hr/positions/branches/{$branch->id}/open-positions", [
            'positions' => [
                [
                    'position_id' => $position->id,
                    'quantity' => 2,
                    'account_type' => 'custom',
                    'account_config' => ['modules' => ['staff-management']],
                ],
                [
                    'position_id' => $position->id,
                    'quantity' => 1,
                    'account_type' => 'custom',
                    'account_config' => ['modules' => ['staff-activity']],
                ],
            ],
        ])->assertCreated();

        $openRequests = PositionOpenRequest::where('branch_id', $branch->id)->orderBy('id')->get();
        $this->assertCount(2, $openRequests);
        $this->assertSame([2, 1], $openRequests->pluck('quantity')->all());
        $this->assertSame('Approved', $openRequests[0]->status);
        $this->assertSame($hr->id, $openRequests[0]->approved_by_user_id);
        $this->assertSame('custom', $openRequests[0]->account_type);
        $this->assertSame(['staff-management'], $openRequests[0]->account_config['modules']);
        $this->assertSame(['staff-activity'], $openRequests[1]->account_config['modules']);

        $this->getJson('/api/hr/positions/branches/awaiting-open-positions')
            ->assertOk()
            ->assertJsonCount(1, 'branches')
            ->assertJsonPath('branches.0.id', $branch->id)
            ->assertJsonPath('branches.0.covered_module_count', 2)
            ->assertJsonPath('branches.0.covered_modules.0.key', 'staff-management')
            ->assertJsonPath('branches.0.covered_modules.0.panel', 'Staff Management')
            ->assertJsonPath('branches.0.remaining_module_count', 18)
            ->assertJsonPath('branches.0.remaining_modules.0.key', 'budget-request-approvals')
            ->assertJsonPath('branches.0.remaining_modules.0.panel', 'Finance');

        $this->postJson("/api/hr/positions/branches/{$branch->id}/open-positions", [
            'positions' => [[
                'position_id' => $adminPosition->id,
                'quantity' => 1,
                'account_type' => 'standard',
            ]],
        ])->assertCreated();

        $this->getJson('/api/hr/positions/branches/awaiting-open-positions')
            ->assertOk()
            ->assertJsonCount(1, 'branches')
            ->assertJsonPath('branches.0.id', $branch->id)
            ->assertJsonPath('branches.0.covered_module_count', 6)
            ->assertJsonPath('branches.0.remaining_module_count', 14);

        $remainingModules = [
            'budget-request-approvals',
            'current-price-markup',
            'receipt-approval',
            'financial-overview',
            'recent-transactions',
            'manual-procurement',
            'procurement-requests',
            'add-supplier',
            'supplier-products',
            'stock-confirmation-history',
            'product-barcodes',
            'pending-stock',
            'inventory-monitor',
            'product-disposal',
        ];

        $this->postJson("/api/hr/positions/branches/{$branch->id}/open-positions", [
            'positions' => [[
                'position_id' => $position->id,
                'quantity' => 1,
                'account_type' => 'custom',
                'account_config' => ['modules' => $remainingModules],
            ]],
        ])->assertCreated();

        $this->getJson('/api/hr/positions/branches/awaiting-open-positions')
            ->assertOk()
            ->assertJsonCount(0, 'branches');

        $this->postJson("/api/hr/positions/branches/{$branch->id}/open-positions", [
            'positions' => [[
                'position_id' => $position->id,
                'quantity' => 1,
                'account_type' => 'custom',
                'account_config' => ['modules' => ['staff-management']],
            ]],
        ])->assertStatus(409);

        $this->assertDatabaseCount('position_open_requests', 4);

        $branchHr = User::create([
            'username' => 'branch_hr_openings',
            'email' => 'branch-hr-openings@example.com',
            'password' => 'Password123!',
            'full_name' => 'Branch HR',
            'role' => 'HR',
            'department' => 'HR',
            'branch_id' => $branch->id,
            'is_active' => true,
            'must_change_password' => false,
        ]);

        $this->actingAs($branchHr)
            ->getJson('/api/hr/positions/branches/awaiting-open-positions')
            ->assertForbidden();
    }

    public function test_inactive_positions_are_not_listed_or_requestable(): void
    {
        $hr = User::create([
            'username' => 'hr_inactive_position',
            'email' => 'hr-inactive-position@example.com',
            'password' => 'Password123!',
            'full_name' => 'HR User',
            'role' => 'HR',
            'department' => 'HR',
            'is_active' => true,
            'must_change_password' => false,
        ]);

        $inactivePosition = \App\Models\Position::create([
            'name' => 'Branch Manager',
            'department' => null,
            'is_active' => false,
        ]);
        \App\Models\Position::create([
            'name' => 'Server/Staff',
            'department' => null,
            'is_active' => false,
        ]);
        \App\Models\Position::create([
            'name' => 'Cashier',
            'department' => 'CASHIER',
            'is_active' => true,
        ]);

        $this->actingAs($hr)
            ->getJson('/api/hr/positions')
            ->assertOk()
            ->assertJsonCount(1, 'positions')
            ->assertJsonPath('positions.0.name', 'Cashier');

        $this->postJson('/api/hr/positions/requests', [
            'position_id' => $inactivePosition->id,
            'quantity' => 1,
        ])->assertUnprocessable();

        $this->assertDatabaseCount('position_open_requests', 0);
    }

    public function test_branch_budget_and_invoice_total_are_deducted_after_owner_approval(): void
    {
        $mainBranch = Branch::create([
            'code' => 'HQ',
            'name' => 'Main Branch',
            'is_main_branch' => true,
            'is_active' => true,
            'approval_status' => 'approved',
            'budget' => 300000,
        ]);

        $financeUser = User::create([
            'username' => 'main_finance',
            'email' => 'mainfinance@example.com',
            'password' => 'Password123!',
            'full_name' => 'Main Finance',
            'role' => 'MANAGER',
            'department' => 'FINANCE',
            'branch_id' => $mainBranch->id,
            'is_active' => true,
        ]);

        $owner = User::create([
            'username' => 'branch_owner',
            'email' => 'branchowner@example.com',
            'password' => 'Password123!',
            'full_name' => 'Branch Owner',
            'role' => 'OWNER',
            'is_active' => true,
        ]);

        $branch = Branch::create([
            'code' => 'BR01',
            'name' => 'New Branch',
            'is_main_branch' => false,
            'is_active' => false,
            'approval_status' => 'pending_finance',
            'budget' => 100000,
            'total_investment' => 120000.50,
        ]);

        $this->actingAs($financeUser)
            ->postJson("/api/main-branch/finance/branch-requests/{$branch->id}/approve")
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['budget']);

        $this->postJson("/api/main-branch/finance/branch-requests/{$branch->id}/approve", ['budget' => 180000])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'The branch budget and invoice total cannot exceed the available Main Branch budget.')
            ->assertJsonPath('available_main_branch_budget', 300000)
            ->assertJsonPath('max_assignable_budget', 179879);

        $this->postJson("/api/main-branch/finance/branch-requests/{$branch->id}/approve", ['budget' => 100000])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertSame(300000, (int) $mainBranch->fresh()->budget);
        $this->assertSame('pending_owner', $branch->fresh()->approval_status);
        $this->assertSame(100000, (int) $branch->fresh()->budget);
        $this->assertSame(100000, (int) $branch->fresh()->initial_budget);

        $this->actingAs($owner)
            ->postJson("/api/owner/branch-requests/{$branch->id}/approve")
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertEquals(79999.5, (float) $mainBranch->fresh()->budget);
        $this->assertSame('approved', $branch->fresh()->approval_status);
        $this->assertTrue((bool) $branch->fresh()->is_active);
    }

    public function test_finance_branch_rejection_requires_a_reason_and_exposes_it_to_main_branch_admin(): void
    {
        $mainBranch = Branch::create([
            'code' => 'HQ',
            'name' => 'Main Branch',
            'is_main_branch' => true,
            'is_active' => true,
            'approval_status' => 'approved',
            'budget' => 300000,
        ]);

        $financeUser = User::create([
            'username' => 'finance_rejector',
            'email' => 'finance-rejector@example.com',
            'password' => 'Password123!',
            'role' => 'MANAGER',
            'department' => 'FINANCE',
            'branch_id' => $mainBranch->id,
            'is_active' => true,
        ]);

        $mainBranchAdmin = User::create([
            'username' => 'main_admin',
            'email' => 'main-admin@example.com',
            'password' => 'Password123!',
            'role' => 'ADMIN',
            'branch_id' => $mainBranch->id,
            'is_active' => true,
        ]);

        $branch = Branch::create([
            'code' => 'FR01',
            'name' => 'Finance Rejected Branch',
            'is_active' => false,
            'approval_status' => 'pending_finance',
            'budget' => 100000,
        ]);

        $this->actingAs($financeUser)
            ->postJson("/api/main-branch/finance/branch-requests/{$branch->id}/reject")
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['reason']);

        $reason = 'Insufficient budget or financial justification';
        $this->actingAs($financeUser)
            ->postJson("/api/main-branch/finance/branch-requests/{$branch->id}/reject", ['reason' => $reason])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
            'approval_status' => 'rejected',
            'rejection_reason' => $reason,
        ]);

        $this->actingAs($mainBranchAdmin)
            ->getJson('/api/superadmin/branches')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $branch->id,
                'rejection_reason' => $reason,
            ]);
    }

    public function test_owner_branch_rejection_requires_and_stores_a_reason(): void
    {
        $owner = User::create([
            'username' => 'branch_reject_owner',
            'email' => 'branch-reject-owner@example.com',
            'password' => 'Password123!',
            'role' => 'OWNER',
            'is_active' => true,
        ]);

        $branch = Branch::create([
            'code' => 'OR01',
            'name' => 'Owner Rejected Branch',
            'is_active' => false,
            'approval_status' => 'pending_owner',
            'budget' => 100000,
            'finance_confirmed_by' => $owner->id,
            'finance_confirmed_at' => now(),
        ]);

        $this->actingAs($owner)
            ->postJson("/api/owner/branch-requests/{$branch->id}/reject", ['reason' => 'Other'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['reason_details']);

        $this->actingAs($owner)
            ->postJson("/api/owner/branch-requests/{$branch->id}/reject", [
                'reason' => 'Other',
                'reason_details' => 'The proposed location is not suitable.',
            ])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
            'approval_status' => 'rejected',
            'rejection_reason' => 'Other: The proposed location is not suitable.',
        ]);
    }

    public function test_branch_cannot_be_reactivated_before_finance_and_owner_approval(): void
    {
        $mainBranch = Branch::create([
            'code' => 'HQ',
            'name' => 'Main Branch',
            'is_main_branch' => true,
            'is_active' => true,
            'approval_status' => 'approved',
            'budget' => 300000,
        ]);

        $mainBranchAdmin = User::create([
            'username' => 'approval_gate_admin',
            'email' => 'approval-gate-admin@example.com',
            'password' => 'Password123!',
            'role' => 'ADMIN',
            'branch_id' => $mainBranch->id,
            'is_active' => true,
        ]);

        foreach (['pending_finance', 'pending_owner', 'rejected'] as $status) {
            $branch = Branch::create([
                'code' => strtoupper(substr($status, 0, 3)) . '01',
                'name' => ucfirst(str_replace('_', ' ', $status)),
                'is_active' => false,
                'approval_status' => $status,
                'budget' => 100000,
            ]);

            $this->actingAs($mainBranchAdmin)
                ->patchJson("/api/superadmin/branches/{$branch->id}/reactivate")
                ->assertStatus(422)
                ->assertJsonPath('ok', false);

            $this->assertFalse((bool) $branch->fresh()->is_active);
        }
    }
}
