<?php
session_start();

// التحقق من أن المستخدم قد سجل الدخول
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'parent') {
    header("Location: login.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - ولي أمر</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>مرحباً بك في لوحة تحكم ولي الأمر</h2>
        <p>هنا يمكنك متابعة حالة طفلك.</p>

        <a href="logout.php" class="btn btn-danger">تسجيل الخروج</a>
    </div>
</body>
</html>