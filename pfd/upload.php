<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) {
    header('Location: logind.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['child_file'])) {
    $childId = $_POST['child_id'] ?? 0;
    $fileName = $_FILES['child_file']['name'];
    $fileTmp = $_FILES['child_file']['tmp_name'];
    $fileSize = $_FILES['child_file']['size'];
    $fileError = $_FILES['child_file']['error'];
    
    // التحقق من وجود الطفل
    try {
        $stmt = $pdo->prepare("SELECT id FROM children WHERE id = ? AND parent_id = ?");
        $stmt->execute([$childId, $_SESSION['user_id']]);
        $child = $stmt->fetch();
        
        if (!$child) {
            die('غير مسموح برفع الملفات لهذا الطفل');
        }
        
        // إنشاء مجلد التحميلات إذا لم يكن موجوداً
        $uploadDir = 'uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // إنشاء اسم فريد للملف
        $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
        $newFileName = uniqid() . '.' . $fileExt;
        $uploadPath = $uploadDir . $newFileName;
        
        // نقل الملف إلى مجلد التحميلات
        if (move_uploaded_file($fileTmp, $uploadPath)) {
            // حفظ معلومات الملف في قاعدة البيانات
            $stmt = $pdo->prepare("INSERT INTO child_files (child_id, file_name, file_path, uploaded_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$childId, $fileName, $uploadPath]);
            
            header('Location: child-profile.php?upload=success');
            exit;
        } else {
            die('حدث خطأ أثناء رفع الملف');
        }
        
    } catch (PDOException $e) {
        die('حدث خطأ في السيرفر: ' . $e->getMessage());
    }
} else {
    header('Location: child-profile.php');
    exit;
}
?>