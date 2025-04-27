<?php
// تمكين عرض الأخطاء
ini_set('display_errors', 1);
error_reporting(E_ALL);

// الاتصال بقاعدة البيانات
include('config.php'); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        // إذا كانت هناك مشكلة في التحضير للاستعلام
        die('خطأ في الاستعلام: ' . $conn->error);
    }

    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] == 'parent') {
            header("Location: parent-dashboard.php");
            exit();
        } else {
            header("Location: specialist-dashboard.php");
            exit();
        }
    } else {
        echo "<script>alert('البريد الإلكتروني أو كلمة المرور غير صحيحة');</script>";
    }
}
?>