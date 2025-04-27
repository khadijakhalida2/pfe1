<?php
// إعدادات الاتصال بقاعدة البيانات
$servername = "localhost";
$username = "root";  // تأكد من أن المستخدم هو 'root' إذا كنت تستخدم XAMPP
$password = "";  // إذا كنت تستخدم XAMPP، عادةً لا يكون هناك كلمة مرور افتراضية
$dbname = "Nutqona_db";  // اسم قاعدة البيانات الصحيح هنا

// إنشاء الاتصال
$conn = new mysqli($servername, $username, $password, $dbname);

// التحقق من الاتصال
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}
?>