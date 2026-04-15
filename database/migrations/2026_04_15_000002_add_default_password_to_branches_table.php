<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            if (!Schema::hasColumn('branches', 'default_password')) {
                $table->string('default_password')->nullable()->after('budget')->comment('Current default password for branch staff accounts');
            }
            if (!Schema::hasColumn('branches', 'default_password_updated_at')) {
                $table->timestamp('default_password_updated_at')->nullable()->after('default_password')->comment('Last time the default password was updated');
            }
        });

        // Generate initial passwords for all existing branches
        $branches = DB::table('branches')->get();
        foreach ($branches as $branch) {
            $password = $this->generateBranchPassword();
            DB::table('branches')
                ->where('id', $branch->id)
                ->update([
                    'default_password' => $password,
                    'default_password_updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            if (Schema::hasColumn('branches', 'default_password_updated_at')) {
                $table->dropColumn('default_password_updated_at');
            }
            if (Schema::hasColumn('branches', 'default_password')) {
                $table->dropColumn('default_password');
            }
        });
    }

    /**
     * Generate a secure random password for a branch
     * Format: BranchDefaultPassword + Date + Random (e.g., BDP20260415ABC123)
     */
    private function generateBranchPassword(): string
    {
        $date = date('Ymd');
        $randomPart = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        return "BDP{$date}{$randomPart}";
    }
};
