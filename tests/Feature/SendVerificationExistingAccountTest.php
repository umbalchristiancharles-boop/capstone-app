<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SendVerificationExistingAccountTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('role')->default('STAFF');
            $table->boolean('is_active')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Mail::fake();
    }

    public function test_authenticated_unverified_user_can_request_code_for_their_own_email(): void
    {
        $user = User::create([
            'username' => 'unverified-user',
            'email' => 'unverified@example.com',
            'role' => 'STAFF',
            'is_active' => true,
        ]);

        $this->postJson('/api/auth/send-verification', ['email' => $user->email], $this->authHeaders($user))
            ->assertOk()
            ->assertJsonPath('email', $user->email);

        $this->assertTrue(Cache::has('verification_code_' . $user->email));
    }

    public function test_unauthenticated_request_cannot_send_code_to_an_existing_account_email(): void
    {
        User::create([
            'username' => 'existing-user',
            'email' => 'existing@example.com',
            'role' => 'STAFF',
            'is_active' => true,
        ]);

        $this->postJson('/api/auth/send-verification', ['email' => 'existing@example.com'])
            ->assertStatus(409)
            ->assertJsonPath('message', 'Email already exists. Please sign in instead.');
    }

    public function test_authenticated_user_cannot_request_another_users_verification_code(): void
    {
        $authenticatedUser = User::create([
            'username' => 'signed-in-user',
            'email' => 'signed-in@example.com',
            'role' => 'STAFF',
            'is_active' => true,
        ]);
        User::create([
            'username' => 'other-user',
            'email' => 'other@example.com',
            'role' => 'STAFF',
            'is_active' => true,
        ]);

        $this->postJson(
            '/api/auth/send-verification',
            ['email' => 'other@example.com'],
            $this->authHeaders($authenticatedUser)
        )
            ->assertStatus(409)
            ->assertJsonPath('message', 'Email already exists. Please sign in instead.');
    }

    private function authHeaders(User $user): array
    {
        return ['Authorization' => 'Bearer ' . $user->createToken('verification-test')->plainTextToken];
    }
}
