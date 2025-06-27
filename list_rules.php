<?php
require 'db.php';

$res = $db->query("SELECT * FROM firewall_events ORDER BY time DESC");

echo "<table border='1' style='width:100%; text-align:center;'><tr><th>عنوان IP</th><th>الإجراء</th><th>الوقت</th></tr>";
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    echo "<tr><td>{$row['src_ip']}</td><td>{$row['action']}</td><td>{$row['time']}</td></tr>";
}
echo "</table>";
?>
