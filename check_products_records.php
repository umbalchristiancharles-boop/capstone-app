<?php
// Simple PDO-based query
use Dotenv\Dotenv;

require 'vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$driver = getenv('DB_CONNECTION') ?: 'mysql';
$host = getenv('DB_HOST') ?: '127.0.0.1';
$database = getenv('DB_DATABASE') ?: 'chikintayo_db';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$port = getenv('DB_PORT') ?: '3306';

try {
    if ($driver === 'sqlite') {
        $dbPath = $database;
        if (!file_exists($dbPath)) {
            $dbPath = __DIR__ . '/database/database.sqlite';
        }
        $pdo = new PDO("sqlite:" . $dbPath);
    } else {
        // For MySQL, try with error mode to get better details
        $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password ?: null);
    }
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connected successfully\n";
    echo "Driver: $driver | Database: $database\n\n";
    
    // Check if products table exists using information_schema
    if ($driver === 'mysql') {
        $stmt = $pdo->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='products'");
    } else {
        $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='products'");
    }
    
    $tableExists = $stmt->fetchColumn();
    
    echo "Products table exists: " . ($tableExists ? "YES ✅" : "NO ❌") . "\n\n";
    
    if ($tableExists) {
        // Get count of all products
        $countStmt = $pdo->query("SELECT COUNT(*) FROM products");
        $totalCount = $countStmt->fetchColumn();
        echo "Total products in table: $totalCount\n\n";
        
        if ($totalCount > 0) {
            // Get first 10 products
            $stmt = $pdo->query("SELECT id, name FROM products LIMIT 10");
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "First 10 products:\n";
            echo str_pad("ID", 5) . str_pad("Name", 50) . "\n";
            echo str_repeat("-", 60) . "\n";
            
            foreach ($products as $product) {
                echo str_pad($product['id'] ?? 'NULL', 5) . str_pad($product['name'] ?? 'NULL', 50) . "\n";
            }
            
            // Check specifically for IDs 1-6
            echo "\n\nChecking for specific product IDs (1-6):\n";
            echo str_repeat("-", 30) . "\n";
            for ($id = 1; $id <= 6; $id++) {
                $stmt = $pdo->prepare("SELECT EXISTS(SELECT 1 FROM products WHERE id = ?)");
                $stmt->execute([$id]);
                $exists = $stmt->fetchColumn();
                echo "ID $id: " . ($exists ? "✅ EXISTS" : "❌ MISSING") . "\n";
            }
        } else {
            echo "⚠️  Products table is EMPTY - no records found!\n";
            echo "This is the root cause of the 422 validation error.\n";
            echo "The validation rule 'exists:products,id' is failing because those product IDs don't exist.\n";
        }
    } else {
        echo "❌ Products table DOES NOT EXIST\n";
        echo "This is the root cause of the 422 validation error.\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database Connection Error: " . $e->getMessage() . "\n";
    echo "\nDebug Info:\n";
    echo "Driver: $driver\n";
    echo "Host: $host\n";
    echo "Database: $database\n";
    echo "Port: $port\n";
    echo "Username: $username\n";
    
    // Try to connect without specifying database to check if MySQL is running
    echo "\n\nAttempting to connect to MySQL server (without database)...\n";
    try {
        $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $username, $password ?: null);
        echo "✅ MySQL server is running\n";
        
        // List available databases
        $stmt = $pdo->query("SHOW DATABASES");
        $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "\nAvailable databases:\n";
        foreach ($databases as $db) {
            echo "  - $db\n";
        }
    } catch (PDOException $e2) {
        echo "❌ Cannot connect to MySQL server: " . $e2->getMessage() . "\n";
    }
}
