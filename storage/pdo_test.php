<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=chikintayo_db', 'root', '');
    $version = $pdo->query('select version()')->fetchColumn();
    echo "PDO OK - MySQL version: {$version}\n";
} catch (PDOException $e) {
    echo "PDO ERROR: " . $e->getMessage() . "\n";
}
