<?php
require_once 'config.php';
checkParentAuth();

try {
    $db = getDBConnection();
    
    // جلب بيانات الطفل
    $stmt = $db->prepare("SELECT * FROM children WHERE id = ? AND parent_id = ?");
    $stmt->execute([$_SESSION['child_id'], $_SESSION['user_id']]);
    $child = $stmt->fetch();
    
    if (!$child) {
        header('Location: parent-dashboard.php');
        exit;
    }
    
    // جلب جميع التقارير
    $stmt = $db->prepare("
        SELECT r.*, s.name as specialist_name 
        FROM progress_reports r
        JOIN specialists s ON r.specialist_id = s.id
        WHERE r.child_id = ?
        ORDER BY r.report_date DESC
    ");
    $stmt->execute([$child['id']]);
    $reports = $stmt->fetchAll();
    
    // جلب جميع الجلسات
    $stmt = $db->prepare("
        SELECT s.*, sp.name as specialist_name 
        FROM sessions s
        JOIN specialists sp ON s.specialist_id = sp.id
        WHERE s.child_id = ?
        ORDER BY s.session_date DESC
    ");
    $stmt->execute([$child['id']]);
    $sessions = $stmt->fetchAll();

} catch (PDOException $e) {
    die("حدث خطأ في قاعدة البيانات: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ملف الطفل - نُطقنا</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #85bae2;
            --primary-green: #92cac7;
        }
        
        .child-header {
            background: linear-gradient(to right, #85bae2, #92cac7);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        
        .progress-bar {
            height: 20px;
            border-radius: 10px;
        }
        
        .report-card {
            border-left: 4px solid var(--primary-blue);
            transition: all 0.3s;
        }
        
        .session-card {
            border-left: 4px solid var(--primary-green);
        }
    </style>
</head>
<body>
    <!-- ... (نفس شريط التنقل والقائمة الجانبية من parent-dashboard.php) ... -->

    <div class="main-content">
        <!-- بطاقة الطفل -->
        <div class="card mb-4">
            <div class="child-header p-4">
                <div class="row align-items-center">
                    <div class="col-md-2 text-center">
                        <img src="assets/images/child-avatar.png" alt="صورة الطفل" class="rounded-circle" width="80">
                    </div>
                    <div class="col-md-10">
                        <h3><?= htmlspecialchars($child['name']) ?></h3>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <p><strong>العمر:</strong> <?= htmlspecialchars($child['age']) ?> سنوات</p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>مستوى التوحد:</strong> <?= htmlspecialchars($child['autism_level']) ?></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>تاريخ التسجيل:</strong> <?= date('Y/m/d', strtotime($child['created_at'])) ?></p>
                    </div>
                </div>
                
                <!-- شريط تقدم الحالة -->
                <div class="mt-4">
                    <h5>تقدم الحالة</h5>
                    <div class="progress mb-2" style="height: 20px;">
                        <div class="progress-bar bg-success" style="width: 65%">65%</div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <span class="badge bg-danger">ضعيف</span>
                        </div>
                        <div class="col text-center">
                            <span class="badge bg-warning">متوسط</span>
                        </div>
                        <div class="col text-end">
                            <span class="badge bg-success">جيد</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- التقارير -->
        <h4 class="mb-3">التقارير</h4>
        <div class="row mb-4">
            <?php if (!empty($reports)): ?>
                <?php foreach ($reports as $report): ?>
                <div class="col-md-6 mb-3">
                    <div class="report-card p-3 h-100">
                        <div class="d-flex justify-content-between">
                            <h5>تقرير <?= date('Y/m/d', strtotime($report['report_date'])) ?></h5>
                            <span class="badge bg-primary"><?= htmlspecialchars($report['progress_level']) ?></span>
                        </div>
                        <p class="text-muted">الأخصائي: <?= htmlspecialchars($report['specialist_name']) ?></p>
                        <p><?= nl2br(htmlspecialchars(substr($report['notes'], 0, 200))) ?>...</p>
                        <a href="report-details.php?id=<?= $report['id'] ?>" class="btn btn-outline-primary btn-sm">عرض التفاصيل</a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">لا توجد تقارير متاحة</div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- الجلسات -->
        <h4 class="mb-3">سجل الجلسات</h4>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>التاريخ</th>
                        <th>الوقت</th>
                        <th>الأخصائي</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($sessions)): ?>
                        <?php foreach ($sessions as $session): ?>
                        <tr>
                            <td><?= date('Y/m/d', strtotime($session['session_date'])) ?></td>
                            <td><?= substr($session['start_time'], 0, 5) ?> - <?= substr($session['end_time'], 0, 5) ?></td>
                            <td><?= htmlspecialchars($session['specialist_name']) ?></td>
                            <td>
                                <?php if ($session['status'] === 'مجدولة'): ?>
                                    <span class="badge bg-warning">مجدولة</span>
                                <?php elseif ($session['status'] === 'منتهية'): ?>
                                    <span class="badge bg-success">منتهية</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">ملغاة</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="session-details.php?id=<?= $session['id'] ?>" class="btn btn-sm btn-outline-primary">التفاصيل</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">لا توجد جلسات مسجلة</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>