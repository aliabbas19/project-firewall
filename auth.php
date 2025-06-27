<?php
session_start();
require_once("db.php");

$username = $_POST['username'];
$password = $_POST['password'];

$stmt = $db->prepare("SELECT * FROM users WHERE username = :u AND password = :p");
$stmt->bindValue(":u", $username);
$stmt->bindValue(":p", $password);
$result = $stmt->execute();

if ($result->fetchArray()) {
    $_SESSION['loggedin'] = true;
    $_SESSION['username'] = $username;
    header("Location: dashboard.php");
    exit;
} else {
    echo "❌ اسم المستخدم أو كلمة المرور غير صحيحة.";
}
?>
