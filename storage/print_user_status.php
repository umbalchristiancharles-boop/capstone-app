<?php
// Print user status for debugging login issues
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=chikintayo_db', 'root', '');
$stmt = $pdo->query("SELECT id, username, is_active, deleted_at, password FROM users");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$row['id']} | Username: {$row['username']} | is_active: {$row['is_active']} | deleted_at: {$row['deleted_at']} | Password: {$row['password']}\n";
}
