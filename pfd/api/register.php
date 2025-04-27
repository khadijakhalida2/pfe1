<?php
require_once 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // جمع البيانات من النموذج
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $user_type = $_POST['user_type']; // 'parent' أو 'specialist'
    
    try {
        // إذا كان المستخدم "أولياء الأمور"
        if ($user_type == 'parent') {
            // إدخال بيانات أولياء الأمور
            $sql = "INSERT INTO parents (name, email, password) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $email, $password]);
            
            // تخزين بيانات الولي في الجلسة
            $_SESSION['user_type'] = 'parent';
            $_SESSION['user_email'] = $email;

            // إعادة توجيه المستخدم إلى صفحة لوحة تحكم الولي
            header("Location: parent_dashboard.php");
            exit();
        } 
        // إذا كان المستخدم "أخصائي"
        else if ($user_type == 'specialist') {
            $specialty = $_POST['specialty'];
            $license_number = $_POST['license_number'];
            $years_experience = $_POST['years_experience'];
            // إدخال بيانات الأخصائي
            $sql = "INSERT INTO specialists (name, email, password, specialty, license_number, years_experience) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $email, $password, $specialty, $license_number, $years_experience]);
        }
        echo "تم التسجيل بنجاح!";
    } catch (PDOException $e) {
        echo "خطأ في التسجيل: " . $e->getMessage();
    }
}
?>