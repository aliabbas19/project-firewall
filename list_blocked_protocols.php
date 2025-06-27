<?php
$db = new SQLite3('db.sqlite');
$res = $db->query("SELECT * FROM blocked_protocols ORDER BY time DESC");

echo "<table border='1'><tr><th>البروتوكول</th><th>الوقت</th></tr>";
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    echo "<tr><td>{$row['protocol']}</td><td>{$row['time']}</td></tr>";
}
echo "</table>";
?>

