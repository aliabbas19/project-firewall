<?php
require 'db.php';
$ip = $_POST['ip'] ?? '';
if (filter_var($ip, FILTER_VALIDATE_IP)) {
    shell_exec("sudo /usr/local/bin/nft-control.sh block_ip $ip");

    $stmt = $db->prepare("INSERT INTO firewall_events (src_ip, action, time) VALUES (:ip, 'حضر', datetime('now'))");
    $stmt->bindValue(":ip", $ip);
    $stmt->execute();
}
?>
