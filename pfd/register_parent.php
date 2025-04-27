<?php
header('Content-Type: application/json');
require_once 'config.php';

$response = ['success' => false, 'errors' => []];

// استقبال البيانات
$data = json_decode(file_get_contents('php://input'), true);

// التحقق من البيانات الأساسية
$requiredFields = ['fullName', 'email', 'password', 'confirmPassword', 'user_type'];
foreach ($requiredFields as $field) {
    if (empty($data[$field])) {
        $response['errors'][] = 'حقل ' . $field . ' مطلوب';
    }
}

// تحقق إضافي لبيانات ولي الأمر
if ($data['user_type'] === 'parent') {
    $childFields = ['childName', 'childAge', 'autismLevel'];
    foreach ($childFields as $field) {
        if (empty($data[$field])) {
            $response['errors'][] = 'حقل ' . $field . ' مطلوب لولي الأمر';
        }
    }
}

// تحقق إضافي لبيانات الأخصائي
if ($data['user_type'] === 'specialist') {
    if (empty($data['specialty']) || empty($data['license'])) {
        $response['errors'][] = 'التخصص ورقم الرخصة مطلوبان للأخصائي';
    }
}

// إذا كان هناك أخطاء
if (!empty($response['errors'])) {
    echo json_encode($response);
    exit;
}

try {
    $db = getDBConnection();
    $db->beginTransaction();

    // تسجيل المستخدم في الجدول المناسب
    if ($data['user_type'] === 'parent') {
        // تسجيل ولي الأمر
        $stmt = $db->prepare("INSERT INTO parents (name, email, password) VALUES (?, ?, ?)");
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
        $stmt->execute([$data['fullName'], $data['email'], $hashedPassword]);
        $parentId = $db->lastInsertId();

        // تسجيل الطفل
        $stmt = $db->prepare("INSERT INTO children (parent_id, name, age, autism_level) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $parentId,
            $data['childName'],
            $data['childAge'],
            $data['autismLevel']
        ]);

        $response['redirect'] = 'parent-dashboard.php';
    } else {
        // تسجيل الأخصائي
        $stmt = $db->prepare("INSERT INTO specialists (name, email, password, specialty, license_number) VALUES (?, ?, ?, ?, ?)");
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
        $stmt->execute([
            $data['fullName'],
            $data['email'],
            $hashedPassword,
            $data['specialty'],
            $data['license']
        ]);

        $response['redirect'] = 'specialist-dashboard.php';
    }

    $db->commit();
    $response['success'] = true;
    $response['message'] = 'تم إنشاء الحساب بنجاح';

} catch (PDOException $e) {
    $db->rollBack();
    
    if ($e->getCode() == 23000) {
        $response['errors'][] = 'البريد الإلكتروني مسجل مسبقاً';
    } else {
        $response['errors'][] = 'حدث خطأ في السيرفر: ' . $e->getMessage();
    }
}

echo json_encode($response);
?>