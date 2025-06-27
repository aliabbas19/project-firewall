<?php

$db_file = __DIR__ . '/db.sqlite';

if (!file_exists($db_file)) {
    
    $db = new SQLite3($db_file);

    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY,
        username TEXT NOT NULL,
        password TEXT NOT NULL
    )");

    $db->exec("INSERT INTO users (id, username, password) VALUES (1, 'admin', 'admin')");

    $db->exec("CREATE TABLE IF NOT EXISTS firewall_events (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        src_ip TEXT,
        action TEXT,
        time TEXT
    )");
} else {
    $db = new SQLite3($db_file);
}
?>

