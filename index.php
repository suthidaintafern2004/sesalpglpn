<?php
// index.php (ฉบับแก้ไข)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db_connect.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>แบบบันทึกข้อมูลนิเทศ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="container my-5">
        <div class="card shadow-lg">
            <div class="card-header text-center bg-primary text-white">
                <i class="fas fa-file-alt"></i> <span class="fw-bold">แบบบันทึกข้อมูลผู้นิเทศ และ ผู้รับนิเทศ</span>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="summary.php" onsubmit="return validateSelection(event);">
                    <?php
                    // ส่วนเลือกข้อมูลผู้นิเทศ
                    require_once 'supervisor.php';

                    // ส่วนเลือกข้อมูลผู้รับนิเทศ
                    require_once 'teacher.php';
                    ?>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>