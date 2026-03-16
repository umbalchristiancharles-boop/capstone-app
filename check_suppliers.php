<<?php
require 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Branch;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $pdo = new PDO('mysql:host='.env('DB_HOST').';dbname='.env('DB_DATABASE'), env('DB_USERNAME'), env('DB_PASSWORD'));
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== SUPPLIERS ===\n";
    $stmt = $pdo->query("SELECT id, username, full_name, branch_id, is_active, department, created_at FROM users WHERE role = 'SUPPLIER'");
    $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($suppliers as $s) {
        echo sprintf("ID:%d | %s (%s) | Branch:%s | Active:%s\n", 
            $s['id'], $s['username'], $s['full_name'], $s['branch_id']??'NULL', $s['is_active']?'YES':'NO');
    }
    echo "Total Suppliers: " . count($suppliers) . "\n\n";
    
    echo "=== PROCUREMENT MANAGER ===\n";
    $stmt = $pdo->query("SELECT id, username, full_name, branch_id, department FROM users WHERE role = 'MANAGER' AND department = 'PROCUREMENT'");
    $proc = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($proc as $p) {
        echo sprintf("ID:%d | %s (%s) | Branch:%s | Dept:%s\n", 
            $p['id'], $p['username'], $p['full_name'], $p['branch_id']??'NULL', $p['department']);
    }
    echo "Proc Mgrs: " . count($proc) . "\n\n";
    
    echo "=== BRANCHES ===\n";
    $stmt = $pdo->query("SELECT id, name FROM branches LIMIT 5");
    $branches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($branches as $b) {
        echo sprintf("Branch ID:%d | %s\n", $b['id'], $b['name']);
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>

