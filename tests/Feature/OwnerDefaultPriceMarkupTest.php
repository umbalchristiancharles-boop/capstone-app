<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\PriceMarkupPercentage;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OwnerDefaultPriceMarkupTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['price_markup_percentages', 'price_markup_settings', 'branches', 'users'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('role');
            $table->string('department')->nullable();
            $table->timestamps();
        });

        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_main_branch')->default(false);
            $table->timestamps();
        });

        Schema::create('price_markup_settings', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->decimal('default_percentage', 5, 2)->default(20);
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
        DB::table('price_markup_settings')->insert([
            'id' => 1,
            'default_percentage' => 20,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::create('price_markup_percentages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->decimal('percentage', 5, 2)->default(20);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('set_by')->nullable();
            $table->timestamp('set_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function test_owner_applies_default_markup_to_active_branches_and_persists_it(): void
    {
        $owner = User::create([
            'username' => 'markup-owner',
            'password' => 'password',
            'role' => 'OWNER',
        ]);
        $branchA = Branch::create(['code' => 'A', 'name' => 'Branch A', 'is_active' => true]);
        $branchB = Branch::create(['code' => 'B', 'name' => 'Branch B', 'is_active' => true]);
        $mainBranch = Branch::create([
            'code' => 'MAIN',
            'name' => 'MAIN BRANCH',
            'is_active' => true,
            'is_main_branch' => true,
        ]);
        $inactiveBranch = Branch::create(['code' => 'C', 'name' => 'Inactive Branch', 'is_active' => false]);

        $oldMarkupA = PriceMarkupPercentage::create([
            'branch_id' => $branchA->id,
            'percentage' => 20,
            'is_active' => true,
        ]);
        $oldMarkupB = PriceMarkupPercentage::create([
            'branch_id' => $branchB->id,
            'percentage' => 15,
            'is_active' => true,
        ]);

        $this->actingAs($owner)
            ->getJson('/api/price-markup/default')
            ->assertOk()
            ->assertJsonPath('default_percentage', 20);

        $this->actingAs($owner)
            ->putJson('/api/price-markup/default', ['percentage' => 27.5])
            ->assertOk()
            ->assertJsonPath('default_percentage', 27.5)
            ->assertJsonPath('branches_updated', 2);

        $this->assertFalse($oldMarkupA->fresh()->is_active);
        $this->assertFalse($oldMarkupB->fresh()->is_active);
        $this->assertDatabaseHas('price_markup_percentages', [
            'branch_id' => $branchA->id,
            'percentage' => 27.5,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('price_markup_percentages', [
            'branch_id' => $branchB->id,
            'percentage' => 27.5,
            'is_active' => true,
        ]);
        $this->assertDatabaseMissing('price_markup_percentages', [
            'branch_id' => $mainBranch->id,
            'is_active' => true,
        ]);
        $this->assertDatabaseMissing('price_markup_percentages', [
            'branch_id' => $inactiveBranch->id,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('price_markup_settings', [
            'id' => 1,
            'default_percentage' => 27.5,
        ]);
    }

    public function test_non_owner_cannot_change_the_default_markup(): void
    {
        $manager = User::create([
            'username' => 'markup-manager',
            'password' => 'password',
            'role' => 'MANAGER',
            'department' => 'FINANCE',
        ]);

        $this->actingAs($manager)
            ->putJson('/api/price-markup/default', ['percentage' => 30])
            ->assertForbidden();
    }
}
