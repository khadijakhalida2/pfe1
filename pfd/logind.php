<?php
session_start();
include('db_connection.php'); // تأكد من استبدال هذا بالمسار الفعلي لملف الاتصال بقاعدة البيانات

// التحقق من وجود الطلب (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // الحصول على بيانات المدخلات
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // الاستعلام عن المستخدم في قاعدة البيانات
    $query = "SELECT * FROM parents WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // التحقق من كلمة المرور
        if (password_verify($password, $user['password'])) {
            // تخزين بيانات الجلسة للمستخدم
            $_SESSION['parent_id'] = $user['id'];
            $_SESSION['parent_name'] = $user['name'];

            // استرجاع معلومات الطفل
            $parent_id = $user['id'];
            $child_query = "SELECT * FROM children WHERE parent_id = '$parent_id'";
            $child_result = mysqli_query($conn, $child_query);
            $child_info = mysqli_fetch_assoc($child_result);

            // تخزين معلومات الطفل في الجلسة
            $_SESSION['child_name'] = $child_info['name'];
            $_SESSION['child_age'] = $child_info['age'];
            $_SESSION['child_autism_level'] = $child_info['autism_level'];

            // إعادة التوجيه إلى صفحة الولي مع عرض البيانات
            header('Location: parent_dashboard.php');
            exit;
        } else {
            $error_message = "البريد الإلكتروني أو كلمة المرور غير صحيحة.";
        }
    } else {
        $error_message = "البريد الإلكتروني أو كلمة المرور غير صحيحة.";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>تسجيل الدخول - نُطقنا</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <style>
    /* نفس تنسيق الصفحة التي قدمتها، مع إضافة تنسيقات صغيرة هنا */
    body {
      font-family: 'Tajawal', sans-serif;
    }
    .login-container {
      max-width: 400px;
      margin: auto;
      padding-top: 100px;
    }
  </style>
</head>
<body>

  <div class="login-container">
    <h3 class="text-center mb-4">تسجيل الدخول</h3>

    <?php if (isset($error_message)): ?>
      <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <form method="POST" action="logind.php">
      <div class="mb-3">
        <label for="email" class="form-label">البريد الإلكتروني</label>
        <input type="email" name="email" class="form-control" id="email" required>
      </div>

      <div class="mb-3">
        <label for="password" class="form-label">كلمة المرور</label>
        <input type="password" name="password" class="form-control" id="password" required>
      </div>

      <button type="submit" class="btn btn-primary w-100">تسجيل الدخول</button>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>