<?php
require_once 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use Illuminate\Database\Capsule\Manager as DB;
use App\Models\User;

$db = new DB;
// Same DB config as before
$db->addConnection([
    'driver' => 'mysql',
    'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
    'port' => $_ENV['DB_PORT'] ?? '3306',
    'database' => $_ENV['DB_DATABASE'] ?? 'chikintayo_db',
    'username' => $_ENV['DB_USERNAME'] ?? 'root',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
]);
$db->setAsGlobal();
$db->bootEloquent();

echo "=== User ID 28 (sender) ===\n";
$user = User::find(28);
if ($user) {
    echo "ID: {$user->id}\n";
    echo "Username: {$user->username}\n";
    echo "Role: '{$user->role}' (length: " . strlen($user->role) . ")\n";
    echo "Branch ID: {$user->branch_id}\n";
    echo "Is Active: " . ($user->is_active ? 'yes' : 'no') . "\n";
    echo "Full: " . json_encode($user->toArray(), JSON_PRETTY_PRINT) . "\n";
} else {
    echo "User 28 not found\n";
}

echo "\n=== All users with role like %owner% or %super% ===\n";
$users = User::where('role', 'like', '%owner%')
    ->orWhere('role', 'like', '%super%')
    ->orWhere('role', 'OWNER')
    ->orWhere('role', 'SUPER_ADMIN')
    ->get();
foreach ($users as $u) {
    echo "- ID {$u->id}: '{$u->role}' ({$u->username})\n";
}

?>

