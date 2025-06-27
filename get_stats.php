<?php
header("Content-Type: application/json");

$db = new SQLite3("db.sqlite");



// حساب الإحصائيات من الجداول المنفصلة
$ipCount = $db->querySingle("SELECT COUNT(*) FROM firewall_events");
$portCount = $db->querySingle("SELECT COUNT(*) FROM blocked_ports");
$protocolCount = $db->querySingle("SELECT COUNT(*) FROM blocked_protocols");
$customCount = $db->querySingle("SELECT COUNT(*) FROM custom_rules");

// إعادة الإحصائيات كـ JSON
echo json_encode([
  "ip" => $ipCount,
  "port" => $portCount,
  "protocol" => $protocolCount,
  "custom" => $customCount
]);
?>

