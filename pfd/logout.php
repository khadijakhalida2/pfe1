<?php
session_start();
session_destroy(); // تدمير الجلسة
header("Location: login.php"); // إعادة توجيه المستخدم إلى صفحة تسجيل الدخول
exit();
?>