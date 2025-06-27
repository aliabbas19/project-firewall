<?php
$db = new SQLite3("db.sqlite");

$port = $_POST['port'] ?? '';
$direction = $_POST['direction'] ?? '';

if (!is_numeric($port) || !in_array($direction, ['in', 'out'])) {
    exit("بيانات غير صالحة.");
}

// تنفيذ أمر nftables
$escapedPort = escapeshellarg($port);
$escapedDir = escapeshellarg($direction);
exec("sudo /usr/local/bin/nft-control.sh block_port $escapedPort $escapedDir");

// حفظ في قاعدة البيانات
$stmt = $db->prepare("INSERT INTO blocked_ports (port, direction, time) VALUES (?, ?, datetime('now'))");
$stmt->bindValue(1, $port, SQLITE3_INTEGER);
$stmt->bindValue(2, $direction, SQLITE3_TEXT);
$stmt->execute();

echo "✅ تم حظر المنفذ $port ($direction)";
?>

