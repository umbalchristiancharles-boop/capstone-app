<?php
$hash = '$2y$10$hBi87sFwm35A4fllgLbWaOl8ABKBaWyNj1sjMjeN49MkJ1A/H//fe';
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=chikintayo_db', 'root', '');
    $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
    $stmt->execute([$hash, 11]);
    echo "Updated OK\n";
    $row = $pdo->query('SELECT id, username, password_hash FROM users WHERE id = 11')->fetch(PDO::FETCH_ASSOC);
    echo "User: {$row['username']}\n";
    echo "Hash length: " . strlen($row['password_hash']) . "\n";
} catch (PDOException $e) {
    echo "PDO ERROR: " . $e->getMessage() . "\n";
}
