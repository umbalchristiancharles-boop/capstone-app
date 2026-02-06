<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

try {
    // Check if admin already exists
    $existingAdmin = User::where('email', 'admin@chikintayo.com')->first();
    if ($existingAdmin) {
        echo "✓ Admin already exists\n";
        echo "  Email: " . $existingAdmin->email . "\n";
        echo "  Username: " . $existingAdmin->username . "\n";
        echo "  Role: " . $existingAdmin->role . "\n";
        exit(0);
    }

    // Create new admin user
    $admin = User::create([
        'username' => 'admin',
        'email' => 'admin@chikintayo.com',
        'password' => 'Admin123!',
        'full_name' => 'Administrator',
        'role' => 'OWNER',
        'is_active' => true,
        'must_change_password' => false,
    ]);

    echo "✓ Admin user created successfully\n";
    echo "  Email: " . $admin->email . "\n";
    echo "  Username: " . $admin->username . "\n";
    echo "  Password: Admin123!\n";
    echo "  Role: " . $admin->role . "\n\n";
    echo "You can now login with:\n";
    echo "  Email: admin@chikintayo.com\n";
    echo "  Password: Admin123!\n";

} catch (Exception $e) {
    echo "✗ Error creating admin: " . $e->getMessage() . "\n";
    exit(1);
}
