<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MainBranchLogisticsAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('name')->nullable();
            $table->boolean('is_main_branch')->default(false);
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('password')->nullable();
            $table->string('role');
            $table->string('department')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('full_name')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function test_main_branch_logistics_manager_can_select_all_branches(): void
    {
        $mainBranch = Branch::forceCreate([
            'id' => 32,
            'code' => 'HQ',
            'name' => 'Headquarters',
            'is_main_branch' => true,
        ]);
        $branch = Branch::forceCreate([
            'id' => 33,
            'code' => 'BR-1',
            'name' => 'Branch One',
        ]);
        $manager = User::create([
            'username' => 'logistics-main',
            'role' => 'MANAGER',
            'department' => 'LOGISTICS',
            'branch_id' => $mainBranch->id,
            'is_active' => true,
        ]);

        $this->actingAs($manager)
            ->getJson('/api/manager/logistics/profile')
            ->assertOk()
            ->assertJsonPath('user.can_select_branch', true);

        $this->actingAs($manager)
            ->getJson('/api/manager/logistics/branches')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['id' => $branch->id, 'name' => 'Branch One']);
    }

    public function test_regular_branch_logistics_manager_remains_limited_to_own_branch(): void
    {
        $branch = Branch::forceCreate([
            'id' => 33,
            'code' => 'BR-1',
            'name' => 'Branch One',
        ]);
        Branch::forceCreate([
            'id' => 34,
            'code' => 'BR-2',
            'name' => 'Branch Two',
        ]);
        $manager = User::create([
            'username' => 'logistics-branch',
            'role' => 'MANAGER',
            'department' => 'LOGISTICS',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);

        $this->actingAs($manager)
            ->getJson('/api/manager/logistics/profile')
            ->assertOk()
            ->assertJsonPath('user.can_select_branch', false);

        $this->actingAs($manager)
            ->getJson('/api/manager/logistics/branches')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $branch->id);
    }
}
