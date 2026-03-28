<?php
// fix_logistics_manager.php - MANAGER LOGISTICS 401 FIX
// Usage: cd c:/xampp/htdocs/capstone-app && php tools/fix_logistics_manager.php

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

echo "=== MANAGER LOGISTICS 401 FIX ===\n\n";

// 1. LIST EXISTING MANAGERS
echo "EXISTING MANAGERS:\n";
echo str_repeat('=', 50)."\n";
$managers = User::where('role', 'like', '%MANAGER%')
    ->where('is_active', 1)
    ->orderBy('department', 'asc')
    ->get(['id', 'username', 'full_name', 'role', 'department', 'branch_id']);

foreach ($managers as $m) {
    echo sprintf("ID:%-4d | %-15s | %-20s | DEPT: %-12s | BRANCH:%-2d\n", 
        $m->id, $m->username, $m->full_name, 
        $m->department ?: '(NONE)', $m->branch_id ?: 0);
}

echo "\n";

// 2. CREATE/FIX LOGISTICS MANAGER
$logisticsUsername = 'manager_logistics';
$logisticsMgr = User::where('username', $logisticsUsername)
    ->orWhere(function($q) {
        $q->where('role', 'MANAGER')
          ->where('department', 'logistics')
          ->where('is_active', 1);
    })
    ->first();

if (!$logisticsMgr) {
    echo "=== CREATING NEW LOGISTICS MANAGER ===\n";
    
    // Use main branch (ID=1 typically)
    $branchId = 1;
    
    $logisticsMgr = User::create([
        'username' => $logisticsUsername,
        'full_name' => 'Logistics Manager Account',
        'password' => Hash::make('Chikintayo_123'),
        'role' => 'MANAGER',
        'department' => 'LOGISTICS',
        'branch_id' => $branchId,
        'is_active' => 1,
        'must_change_password' => true,
        'email' => 'logistics@chikintayo.local',
    ]);
    
    echo "✅ CREATED: " . $logisticsMgr->username . " (ID: " . $logisticsMgr->id . ")\n";
} else {
    // Fix if needed
    $needsFix = false;
    if ($logisticsMgr->department !== 'LOGISTICS') {
        $logisticsMgr->department = 'LOGISTICS';
        $needsFix = true;
    }
    if (!$logisticsMgr->branch_id) {
        $logisticsMgr->branch_id = 1;
        $needsFix = true;
    }
    if (!$logisticsMgr->is_active) {
        $logisticsMgr->is_active = 1;
        $needsFix = true;
    }
    
    if ($needsFix) {
        $logisticsMgr->save();
        echo "✅ FIXED: " . $logisticsMgr->username . "\n";
    } else {
        echo "✅ EXISTS: " . $logisticsMgr->username . " (ID: " . $logisticsMgr->id . ")\n";
    }
}

echo "\nLOGIN DETAILS:\n";
echo "Username: " . $logisticsUsername . "\n";
echo "Password: Chikintayo_123\n";
echo "Expected redirect: /manager/logistics\n\n";

echo "=== NEXT STEPS ===\n";
echo "1. php artisan route:clear config:clear\n";
echo "2. Visit: http://localhost/manager/logistics (login above)\n";
echo "3. Check logs: tail -f storage/logs/laravel.log | grep 'logistics'\n";
echo "4. Test API calls will now show DEBUG output\n";
echo "5. Clear browser localStorage if needed\n\n";

echo "=== USER SUMMARY ===\n";
echo sprintf("Logistics Mgr: %-15s | DEPT: %-12s | BRANCH: %d\n", 
    $logisticsMgr->username, $logisticsMgr->department, $logisticsMgr->branch_id);

echo "\n✅ READY FOR TESTING!\n";
?>

