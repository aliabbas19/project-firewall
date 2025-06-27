<?php
$db = new SQLite3('/var/www/html/db.sqlite');
$res = $db->query("SELECT * FROM custom_rules ORDER BY added_at DESC");

$rules = [];
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    $rules[] = $row;
}
echo json_encode($rules);
