<?php

namespace App\Services;

use App\Models\Branch;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BranchPasswordService
{
    /**
     * Generate a secure random password for a branch
     * Format: BDP + Date (YYYYMMDD) + Random hex (6 chars)
     * Example: BDP20260415ABC123
     * This ensures the password changes daily and is unique per branch
     */
    public static function generateBranchPassword(): string
    {
        $date = date('Ymd');
        $randomPart = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        return "BDP{$date}{$randomPart}";
    }

    /**
     * Get the current default password for a branch
     * If the password is outdated (not from today), regenerate it
     *
     * @param Branch $branch
     * @return string The current default password for the branch
     */
    public static function getCurrentDefaultPassword(Branch $branch): string
    {
        // Check if password needs to be updated (not updated today)
        if ($branch->default_password_updated_at === null || !$branch->isPasswordFromToday()) {
            return self::updateBranchPassword($branch);
        }

        return $branch->default_password;
    }

    /**
     * Regenerate and save the default password for a branch
     *
     * @param Branch $branch
     * @return string The new default password
     */
    public static function updateBranchPassword(Branch $branch): string
    {
        $newPassword = self::generateBranchPassword();

        $branch->update([
            'default_password' => $newPassword,
            'default_password_updated_at' => now(),
        ]);

        Log::info('Branch default password updated', [
            'branch_id' => $branch->id,
            'branch_name' => $branch->name,
            'updated_at' => now(),
        ]);

        return $newPassword;
    }

    /**
     * Update all branches' default passwords
     * Call this in a scheduled command to update passwords daily
     *
     * @return int Number of branches updated
     */
    public static function updateAllBranchPasswords(): int
    {
        $branches = Branch::where('is_active', true)->get();
        $updatedCount = 0;

        foreach ($branches as $branch) {
            if (!$branch->isPasswordFromToday()) {
                self::updateBranchPassword($branch);
                $updatedCount++;
            }
        }

        Log::info('Daily branch password update completed', [
            'total_branches' => $branches->count(),
            'updated_count' => $updatedCount,
        ]);

        return $updatedCount;
    }

    /**
     * Check if a branch password is from today
     *
     * @param Branch $branch
     * @return bool
     */
    public static function isPasswordFromToday(Branch $branch): bool
    {
        if ($branch->default_password_updated_at === null) {
            return false;
        }

        return Carbon::parse($branch->default_password_updated_at)->isToday();
    }
}
