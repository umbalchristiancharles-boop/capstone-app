<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=chikintayo_db', 'root', '');
    $stmt = $pdo->query("SHOW TABLES LIKE 'attendance_settings'");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($rows) > 0) {
        echo "FOUND\n";
    } else {
        echo "MISSING\n";
    }
    // show migrations table record for migration file
    $m = $pdo->prepare("SELECT * FROM migrations WHERE migration LIKE ?");
    $m->execute(['%create_attendance_settings%']);
    $mrows = $m->fetchAll(PDO::FETCH_ASSOC);
    echo "migrations_records=" . count($mrows) . "\n";
    foreach ($mrows as $r) {
        echo $r['migration'] . "\n";
    }
    // check whether branch id 1 exists
    $b = $pdo->prepare("SELECT id FROM branches WHERE id = 1");
    $b->execute();
    $branchExists = count($b->fetchAll(PDO::FETCH_ASSOC)) > 0;
    echo "branch_1_exists=" . ($branchExists ? 'yes' : 'no') . "\n";

    // check whether attendance_settings row exists for branch 1
    $s = $pdo->prepare("SELECT * FROM attendance_settings WHERE branch_id = 1");
    $s->execute();
    $settingsRows = $s->fetchAll(PDO::FETCH_ASSOC);
    echo "attendance_settings_for_branch_1=" . count($settingsRows) . "\n";
    if (count($settingsRows) > 0) {
        echo json_encode($settingsRows[0]) . "\n";
    }
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
