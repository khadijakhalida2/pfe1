<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: logind.html');
    exit;
}

$fileId = $_GET['id'] ?? 0;

try {
    // التحقق من صلاحيات المستخدم
    $stmt = $pdo->prepare("
        SELECT f.* 
        FROM child_files f
        JOIN children c ON f.child_id = c.id
        WHERE f.id = ? AND c.parent_id = ?
    ");
    $stmt->execute([$fileId, $_SESSION['user_id']]);
    $file = $stmt->fetch();
    
    if (!$file || !file_exists($file['file_path'])) {
        die('الملف غير موجود أو ليس لديك صلاحية للوصول إليه');
    }
    
    // إرسال الملف للتحميل
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file['file_name']) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file['file_path']));
    readfile($file['file_path']);
    exit;
    
} catch (PDOException $e) {
    die('حدث خطأ في السيرفر: ' . $e->getMessage());
}
?>