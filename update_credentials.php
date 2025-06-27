<?php
require 'db.php';

if (isset($_POST['username'], $_POST['password'])) {
    $stmt = $db->prepare("UPDATE users SET username = :u, password = :p WHERE id = 1");
    $stmt->bindValue(":u", $_POST['username']);
    $stmt->bindValue(":p", $_POST['password']);
    $stmt->execute();
    echo "✅ تم تحديث المعلومات بنجاح";
} else {
    echo "❌ فشل في إرسال البيانات";
}
?>

