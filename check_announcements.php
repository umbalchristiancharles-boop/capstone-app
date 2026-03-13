<?php
require_once 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use Illuminate\Database\Capsule\Manager as DB;
use App\Models\Announcement;

$db = new DB;
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
    'strict' => false,
    'engine' => null,
]);

$db->setAsGlobal();
$db->bootEloquent();

echo "=== Announcement Check ===\n";
echo "Total count: " . Announcement::count() . "\n\n";

echo "Recent 5 announcements:\n";
$anns = Announcement::orderBy('created_at', 'desc')->limit(5)->get();
foreach ($anns as $ann) {
    echo "- ID: {$ann->id}, Title: {$ann}";    echo "- Target: {$ann->target}\n";
}

echo "\nTable structure:\n";
$schema = DB::select('DESCRIBE announcements');
print_r($schema);

echo "\nRaw SQL count:\n";
$count = DB::selectOne('SELECT COUNT(*) as cnt FROM announcements');
echo "Count: {$count->cnt}\n";

?>

