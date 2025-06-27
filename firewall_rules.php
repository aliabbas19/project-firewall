<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit;
}
require_once("db.php");
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>إدارة الجدار الناري</title>
</head>
<body>
    <h2>🔥 قواعد الجدار الناري</h2>
    <pre>
    <?php
    $output = shell_exec("sudo /usr/local/bin/nft-control.sh list");
    echo htmlspecialchars($output);
    ?>
    </pre>

    <h3>🚫 حظر عنوان IP</h3>
    <form method="POST">
        <input type="text" name="ip" placeholder="مثال: 192.168.1.10" required>
        <input type="submit" name="block" value="حظر">
    </form>

    <?php
    if (isset($_POST['block'])) {
        $ip = $_POST['ip'];
        shell_exec("sudo /usr/local/bin/nft-control.sh block_ip $ip");

        // تسجل الحظر
        $stmt = $db->prepare("INSERT INTO firewall_events (time, src_ip, dst_ip, protocol, action) VALUES (:t, :src, :dst, :proto, :act)");
        $stmt->bindValue(':t', date('Y-m-d H:i:s'));
        $stmt->bindValue(':src', $ip);
        $stmt->bindValue(':dst', 'firewall');
        $stmt->bindValue(':proto', 'N/A');
        $stmt->bindValue(':act', 'DROP');
        $stmt->execute();

        echo "<p>✅ تم حظر $ip.</p>";
    }
    ?>

    <hr>
    <a href="dashboard.php">⬅️ العودة للوحة التحكم</a>
</body>
</html>
