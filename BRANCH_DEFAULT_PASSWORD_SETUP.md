# Branch Default Password System - Daily Updates

## Overview
This feature implements a system where each branch has its own unique default password that changes automatically every day. This ensures better security and makes it easier to manage staff account creation across multiple branches.

## How It Works

### 1. **Default Password Generation**
- Each branch has a `default_password` field that stores the current day's default password
- Password format: `BDP[YYYYMMDD][6-random-hex-chars]`
  - Example: `BDP20260415ABC123`
- The password automatically regenerates at midnight (00:00) every day
- Each day has a unique password for each branch, even for the same branch

### 2. **Staff Account Creation**
When creating a new staff account:
- If a **branch is specified**: The account is created with that branch's current default password
- If **no branch specified**: A random secure password is generated (for OWNER accounts)
- If a **custom password is provided**: That custom password is used instead

### 3. **Password Reset**
When resetting a staff member's password:
- If the staff is **assigned to a branch**: Their password is reset to that branch's current default password
- If the staff is **not assigned to a branch** (OWNER): Uses the system default password from config

## Files Modified/Created

### New Files
1. **`app/Services/BranchPasswordService.php`** - Service class for password generation and management
2. **`app/Console/Commands/UpdateBranchPasswords.php`** - Artisan command to update all branch passwords
3. **`app/Console/Kernel.php`** - Console kernel with scheduling configuration
4. **`database/migrations/2026_04_15_000002_add_default_password_to_branches_table.php`** - Migration to add password fields

### Modified Files
1. **`app/Models/Branch.php`** - Added password fields and helper method
2. **`app/Http/Controllers/Admin/StaffController.php`** - Updated account creation and password reset logic

## Database Changes

Added two new columns to the `branches` table:
- `default_password` (string): Stores the current day's default password
- `default_password_updated_at` (timestamp): Tracks when the password was last updated

## Usage

### Manual Password Update
To manually update passwords for all branches:
```bash
php artisan branches:update-passwords
```

### Scheduled Updates
The system automatically updates all branch passwords daily at midnight (00:00) via the Laravel scheduler.

To enable scheduled commands, ensure your server runs:
```bash
* * * * * cd /path/to/capstone-app && php artisan schedule:run >> /dev/null 2>&1
```

This cron job should be added to your server's crontab to EVERY MINUTE check if Laravel has scheduled tasks to run. It will then execute the appropriate commands at their scheduled times.

### View Scheduled Commands
To see all scheduled commands:
```bash
php artisan schedule:list
```

## Security Benefits

1. **Daily Password Rotation**: Each branch's password changes daily automatically
2. **Branch Isolation**: Each branch has its own password, preventing cross-branch account creation
3. **Audit Trail**: Passwords are regenerated at predictable times
4. **Compliance**: Supports security policies requiring regular credential changes

## Technical Details

### BranchPasswordService Methods
- `generateBranchPassword()`: Generates a new secure password
- `getCurrentDefaultPassword(Branch $branch)`: Gets current password, regenerates if needed
- `updateBranchPassword(Branch $branch)`: Updates and saves a new password
- `updateAllBranchPasswords()`: Updates passwords for all active branches
- `isPasswordFromToday(Branch $branch)`: Checks if password is from today

### Migration Details
The migration (`2026_04_15_000002_add_default_password_to_branches_table.php`):
- Adds `default_password` and `default_password_updated_at` columns
- Generates initial passwords for all existing branches
- Uses conditional checks to prevent errors if columns already exist
- Provides rollback (down) functionality

## Example Scenario

**Day 1 (April 15, 2026)**
- Branch A default password: `BDP20260415XYZ789`
- New staff created in Branch A gets password: `BDP20260415XYZ789`

**Day 2 (April 16, 2026)**
- At 00:00, all passwords are updated
- Branch A default password: `BDP20260416ABC456`
- New staff created in Branch A gets password: `BDP20260416ABC456`
- Previously created staff still have `BDP20260415XYZ789` (unchanged) until manually reset

## Configuration

### Modify Update Time
To change the time at which passwords update, edit `app/Console/Kernel.php`:
```php
$schedule->command('branches:update-passwords')
         ->dailyAt('22:00')  // Change to 10:00 PM (24-hour format)
```

### Disable Scheduling
To temporarily disable automatic updates:
```php
// In app/Console/Kernel.php, comment out or remove:
// $schedule->command('branches:update-passwords')->dailyAt('00:00');
```

## Testing

### Test Manual Update
```bash
php artisan branches:update-passwords
```

### Test via Command Schedule
```bash
php artisan schedule:run
```

### View Command in List
```bash
php artisan schedule:list
# Output should show:
# branches:update-passwords ..................... every day at 00:00
```

## Troubleshooting

**Issue**: Passwords not updating
- Ensure the cron job is running: `* * * * * cd /path && php artisan schedule:run`
- Check Laravel logs in `storage/logs/`
- Manually run: `php artisan branches:update-passwords`

**Issue**: Staff accounts created with wrong password
- Verify branch assignment for the staff member
- Check `branches.default_password_updated_at` is recent
- Manually run: `php artisan branches:update-passwords`

**Issue**: Cannot reset password
- Ensure staff member has a `branch_id` assigned
- Check the branch still exists and is active
- Verify user has admin permissions
