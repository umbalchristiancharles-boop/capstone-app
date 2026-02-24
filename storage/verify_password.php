<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=chikintayo_db', 'root', '');
    $stmt = $pdo->query('SELECT password_hash FROM users WHERE id = 11');
    $hash = $stmt->fetchColumn();
    echo "Hash: " . substr($hash,0,10) . "... (len=" . strlen($hash) . ")\n";
    if (password_verify('secret123', $hash)) {
        echo "password_verify OK\n";
    } else {
        echo "password_verify FAILED\n";
    }
} catch (PDOException $e) {
    echo "PDO ERROR: " . $e->getMessage() . "\n";
}
