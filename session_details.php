<?php
// ไฟล์: session_details.php
require_once 'db_connect.php';

// 1. ตรวจสอบว่ามี teacher_pid ส่งมาหรือไม่
// ⭐️ แก้ไข: เปลี่ยนจาก $_GET เป็น $_POST ⭐️
if (!isset($_POST['teacher_pid']) || empty($_POST['teacher_pid'])) {
    die("ไม่พบรหัสประจำตัวผู้รับการนิเทศ");
}

$teacher_pid = $_POST['teacher_pid']; // ⭐️ แก้ไข: เปลี่ยนจาก $_GET เป็น $_POST ⭐️
$results = [];
$teacher_info = null;

// 2. ดึงข้อมูลพื้นฐานของครู (ชื่อ และ โรงเรียน) จากข้อมูลการนิเทศล่าสุด
//    เพื่อให้แน่ใจว่ามีข้อมูลแสดง แม้ t_pid จะไม่มีในตาราง teacher โดยตรง
$stmt_teacher = $conn->prepare(
    "SELECT 
        CONCAT(t.PrefixName, t.fname, ' ', t.lname) AS teacher_full_name, 
        s.SchoolName,
        t.adm_name AS teacher_position,
        t.learning_group
     FROM supervision_sessions ss
     LEFT JOIN teacher t ON ss.teacher_t_pid = t.t_pid
     LEFT JOIN school s ON t.school_id = s.school_id
     WHERE ss.teacher_t_pid = ?
     ORDER BY ss.supervision_date DESC
     LIMIT 1"
);
$stmt_teacher->bind_param("s", $teacher_pid);
$stmt_teacher->execute();
$result_teacher = $stmt_teacher->get_result();

if ($result_teacher->num_rows > 0) {
    $teacher_info = $result_teacher->fetch_assoc();
}
$stmt_teacher->close();


// 3. ดึงประวัติการนิเทศทั้งหมดของครูคนนี้ โดยเรียงจากล่าสุดไปเก่าสุด
$sql = "SELECT
            ss.id AS session_id,
            ss.supervision_date,
            ss.inspection_time,
            CONCAT(sp.PrefixName, sp.fname, ' ', sp.lname) AS supervisor_full_name
        FROM
            supervision_sessions ss
        LEFT JOIN
            supervisor sp ON ss.supervisor_p_id = sp.p_id
        WHERE
            ss.teacher_t_pid = ?
        ORDER BY
            ss.supervision_date DESC, ss.id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $teacher_pid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการนิเทศของ <?php echo htmlspecialchars($teacher_info['teacher_full_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>
    <div class="container mt-5">
        <div class="card shadow-lg p-4">
            <h2 class="card-title text-center mb-4"><i class="fas fa-user-clock"></i> ประวัติการนิเทศ</h2>

            <div class="row mb-4">
                <div class="col-md-6">
                    <h5><strong>ผู้รับการนิเทศ:</strong> <?php echo htmlspecialchars($teacher_info['teacher_full_name']); ?></h5>
                </div>
                <div class="col-md-6">
                    <h5><strong>โรงเรียน:</strong> <?php echo htmlspecialchars($teacher_info['SchoolName']); ?></h5>
                </div>
                <div class="col-md-6">
                    <h5><strong>ตำแหน่ง:</strong> <?php echo htmlspecialchars($teacher_info['teacher_position']); ?></h5>
                </div>
                <div class="col-md-6">
                    <h5><strong>กลุ่มสาระฯ:</strong> <?php echo htmlspecialchars($teacher_info['learning_group']); ?></h5>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-primary">
                        <tr class="text-center">
                            <th scope="col" style="width: 20%;">วันที่และเวลา</th>
                            <th scope="col" style="width: 10%;">ครั้งที่นิเทศ</th>
                            <th scope="col" style="width: 25%;">ผู้นิเทศ</th>
                            <th scope="col">การดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($results)) : ?>
                            <tr>
                                <td colspan="4" class="text-center text-danger fw-bold">ไม่พบประวัติการนิเทศสำหรับครูท่านนี้</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($results as $row) : ?>
                                <tr class="text-center">
                                    <td><?php echo (new DateTime($row['supervision_date']))->format('d/m/Y H:i'); ?> น.</td>
                                    <td><?php echo htmlspecialchars($row['inspection_time']); ?></td>
                                    <td><?php echo htmlspecialchars($row['supervisor_full_name']); ?></td>
                                    <td>
                                        <form method="POST" action="supervision_report.php" style="display:inline;">
                                            <input type="hidden" name="session_id" value="<?php echo $row['session_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-primary" title="ดูรายงานผลการนิเทศ"><i class="fas fa-file-alt"></i> ดูรายงาน</button>
                                        </form>
                                        
                                        <button class="btn btn-sm btn-success" disabled title="ยังไม่เปิดใช้งาน"><i class="fas fa-smile-beam"></i> ประเมินความพึงพอใจ</button>
                                        <button class="btn btn-sm btn-warning" disabled title="ยังไม่เปิดใช้งาน"><i class="fas fa-award"></i> พิมพ์เกียรติบัตร</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="text-center mt-4">
                <a href="history.php" class="btn btn-secondary"><i class="fas fa-chevron-left"></i> กลับไปหน้าประวัติ</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>