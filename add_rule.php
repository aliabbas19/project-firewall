<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ip = $_POST['ip'] ?? '';
    $port = $_POST['port'] ?? '';
    $protocol = $_POST['protocol'] ?? '';
    $direction = $_POST['direction'] ?? 'in';

    // حذف المسافات البيضاء
    $ip = trim($ip);
    $port = trim($port);
    $protocol = trim($protocol);
    $direction = trim($direction);

    // تنفيذ سكربت الحظر مع المعطيات
    $cmd = escapeshellcmd("sudo /usr/local/bin/nft-control.sh add_rule '$ip' '$port' '$protocol' '$direction'");
    $output = shell_exec($cmd);

    // حفظ في قاعدة البيانات إذا نجحت الإضافة
    if (strpos($output, 'Error') === false) {
        $db = new SQLite3("db.sqlite");
        $stmt = $db->prepare("INSERT INTO custom_rules (ip, port, protocol, direction) VALUES (?, ?, ?, ?)");
        $stmt->bindValue(1, $ip);
        $stmt->bindValue(2, $port);
        $stmt->bindValue(3, $protocol);
        $stmt->bindValue(4, $direction);
        $stmt->execute();
        echo "تمت إضافة القاعدة.";
    } else {
        echo "حدث خطأ في تنفيذ الأمر:\n$output";
    }
}
?>

