<?php
// Quick script to print the password hash for a user
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=chikintayo_db', 'root', '');
$stmt = $pdo->query("SELECT id, username, password FROM users");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$row['id']} | Username: {$row['username']} | Password: {$row['password']}\n";
}
