<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'chikintayo_db', 3306);
if ($mysqli->connect_errno) {
    fwrite(STDERR, 'CONN_ERR:'.$mysqli->connect_error.PHP_EOL);
    exit(1);
}
$res = $mysqli->query("SELECT id,username,full_name,password,is_active,must_change_password,role,department FROM users WHERE id IN (183,184)");
if (! $res) { fwrite(STDERR, 'Q_ERR:'.$mysqli->error.PHP_EOL); exit(1); }
while ($r = $res->fetch_assoc()) {
    echo json_encode($r).PHP_EOL;
}
$mysqli->close();
