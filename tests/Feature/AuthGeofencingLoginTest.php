<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AuthGeofencingLoginTest extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('geofencing_radius', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('role')->default('STAFF');
            $table->string('full_name')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('must_change_password')->default(false);
            $table->string('department')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function test_login_rejects_user_outside_branch_safe_zone_when_location_is_provided(): void
    {
        $branch = Branch::create([
            'name' => 'Main Branch',
            'address' => 'Test Address',
            'latitude' => 14.5995,
            'longitude' => 120.9842,
            'geofencing_radius' => 100,
            'is_active' => true,
        ]);

        $user = User::create([
            'username' => 'branchuser',
            'password' => Hash::make('Password123'),
            'role' => 'STAFF',
            'full_name' => 'Branch User',
            'branch_id' => $branch->id,
            'is_active' => true,
            'must_change_password' => false,
            'department' => 'KITCHEN',
        ]);

        $response = $this->post('/api/login', [
            'username' => 'branchuser',
            'password' => 'Password123',
            'latitude' => 14.6005,
            'longitude' => 121.0000,
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('message', 'You are outside the safe zone for this branch.');
    }
}
