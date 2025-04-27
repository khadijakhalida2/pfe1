<?php
$host = "localhost"; // أو عنوان الخادم
$dbname = "Nutqona_db";
$username = "root"; // اسم المستخدم
$password = ""; // كلمة السر

try {
    // الاتصال بقاعدة البيانات باستخدام PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage();
}
?>