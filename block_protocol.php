<?php
$db = new SQLite3("db.sqlite");

$protocol = $_POST['protocol'] ?? '';
$direction = $_POST['direction'] ?? '';

$allowed = ['tcp', 'udp', 'icmp'];
if (!in_array($protocol, $allowed) || !in_array($direction, ['in', 'out'])) {
    exit("بيانات غير صالحة.");
}

// تنفيذ أمر nftables
$escapedProto = escapeshellarg($protocol);
$escapedDir = escapeshellarg($direction);
exec("sudo /usr/local/bin/nft-control.sh block_protocol $escapedProto $escapedDir");

// حفظ في قاعدة البيانات
$stmt = $db->prepare("INSERT INTO blocked_protocols (protocol, direction, blocked_at) VALUES (?, ?, datetime('now'))");
$stmt->bindValue(1, $protocol, SQLITE3_TEXT);
$stmt->bindValue(2, $direction, SQLITE3_TEXT);
$stmt->execute();

echo "✅ تم حظر البروتوكول $protocol ($direction)";
?>

