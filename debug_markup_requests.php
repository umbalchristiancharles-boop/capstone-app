<?php
$dsn = 'mysql:host=127.0.0.1;dbname=chikintayo_db';
$pdo = new PDO($dsn, 'root', '');

$result = $pdo->query('SELECT id, branch_id, status, main_finance_approval, owner_approval, created_at FROM price_markup_requests ORDER BY created_at DESC LIMIT 10');
$rows = $result->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
