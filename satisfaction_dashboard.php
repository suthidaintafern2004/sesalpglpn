<?php
// ไฟล์: satisfaction_dashboard.php
require_once 'db_connect.php';

// --- การดึงข้อมูลสำหรับ Dashboard ---
// ดึงข้อมูลคะแนนเฉลี่ยและจำนวนผู้ตอบของแต่ละคำถาม
$sql = "SELECT
            q.id AS question_id,
            q.question_text,
            AVG(ans.rating) AS average_score,
            COUNT(ans.id) AS response_count
        FROM
            satisfaction_questions q
        LEFT JOIN
            satisfaction_answers ans ON q.id = ans.question_id
        GROUP BY
            q.id, q.question_text
        ORDER BY
            q.display_order ASC";

$result = $conn->query($sql);

$dashboard_data = [];
if ($result && $result->num_rows > 0) {
    $item_number = 1; // ⭐️ 1. สร้างตัวแปรนับเลขข้อ
    while ($row = $result->fetch_assoc()) {
        // ⭐️ 2. สร้าง label ใหม่ที่มีเลขข้อนำหน้า (เช่น "1. ความรวดเร็ว...")
        $row['question_text_with_number'] = $item_number . '. ' . $row['question_text'];
        $dashboard_data[] = $row;
        $item_number++;
    }
}

// --- ส่วนดึงข้อมูลสรุปการนิเทศแต่ละโรงเรียน (เพิ่มใหม่) ---
$sql_school_supervision = "SELECT
                                s.SchoolName,
                                COUNT(ss.id) AS supervision_count
                            FROM
                                supervision_sessions ss
                            JOIN
                                teacher t ON ss.teacher_t_pid = t.t_pid
                            JOIN
                                school s ON t.school_id = s.school_id
                            GROUP BY
                                s.SchoolName
                            HAVING
                                COUNT(ss.id) > 0
                            ORDER BY
                                supervision_count DESC";

$result_school = $conn->query($sql_school_supervision);
$school_supervision_data = [];
if ($result_school && $result_school->num_rows > 0) {
    $school_supervision_data = $result_school->fetch_all(MYSQLI_ASSOC);
}

// --- ส่วนดึงข้อมูลสรุปการนิเทศตามตำแหน่งครู (เพิ่มใหม่) ---
$sql_position_supervision = "SELECT
                                t.adm_name AS teacher_position,
                                COUNT(DISTINCT ss.teacher_t_pid) AS supervised_teacher_count
                            FROM
                                supervision_sessions ss
                            JOIN
                                teacher t ON ss.teacher_t_pid = t.t_pid
                            WHERE
                                t.adm_name IS NOT NULL AND t.adm_name != ''
                            GROUP BY
                                t.adm_name
                            ORDER BY
                                supervised_teacher_count DESC";

$result_position = $conn->query($sql_position_supervision);
$position_supervision_data = [];
if ($result_position && $result_position->num_rows > 0) {
    $position_supervision_data = $result_position->fetch_all(MYSQLI_ASSOC);
}

// --- ส่วนดึงข้อมูลสรุปการนิเทศตามกลุ่มสาระ (เพิ่มใหม่) ---
$sql_lg_supervision = "SELECT
                            t.learning_group,
                            COUNT(ss.id) AS supervision_count
                        FROM
                            supervision_sessions ss
                        JOIN
                            teacher t ON ss.teacher_t_pid = t.t_pid
                        WHERE
                            t.learning_group IS NOT NULL AND t.learning_group != ''
                        GROUP BY
                            t.learning_group
                        ORDER BY
                            supervision_count DESC";

$result_lg = $conn->query($sql_lg_supervision);
$lg_supervision_data = [];
if ($result_lg && $result_lg->num_rows > 0) {
    $lg_supervision_data = $result_lg->fetch_all(MYSQLI_ASSOC);
}

$conn->close();


// เตรียมข้อมูลสำหรับ Chart.js
// ⭐️ 3. เปลี่ยนไปใช้ label ที่มีเลขข้อ
$chart_labels = json_encode(array_column($dashboard_data, 'question_text_with_number'));

// FIX: จัดการกับค่า NULL ที่อาจเกิดขึ้นจาก AVG() เมื่อไม่มีข้อมูล
// แปลงค่า NULL ทั้งหมดใน array ให้เป็น 0 เพื่อให้ Chart.js ทำงานได้
$scores = array_map(function($score) {
    return $score ?? 0; // ถ้า $score เป็น NULL ให้ใช้ 0 แทน
}, array_column($dashboard_data, 'average_score'));

$chart_values = json_encode($scores);

// เตรียมข้อมูลสำหรับกราฟสรุปการนิเทศแต่ละโรงเรียน (เพิ่มใหม่)
$school_chart_labels = json_encode(array_column($school_supervision_data, 'SchoolName'));
$school_chart_values = json_encode(array_column($school_supervision_data, 'supervision_count'));

// เตรียมข้อมูลสำหรับกราฟสรุปตามตำแหน่ง (เพิ่มใหม่)
$position_chart_labels = json_encode(array_column($position_supervision_data, 'teacher_position'));
$position_chart_values = json_encode(array_column($position_supervision_data, 'supervised_teacher_count'));

// เตรียมข้อมูลสำหรับกราฟสรุปตามกลุ่มสาระ (เพิ่มใหม่)
$lg_chart_labels = json_encode(array_column($lg_supervision_data, 'learning_group'));
$lg_chart_values = json_encode(array_column($lg_supervision_data, 'supervision_count'));

// 🎨 Define colors in PHP to be used in both legend and chart
$background_colors = [
    'rgba(255, 99, 132, 0.7)',
    'rgba(54, 162, 235, 0.7)',
    'rgba(255, 206, 86, 0.7)',
    'rgba(75, 192, 192, 0.7)',
    'rgba(153, 102, 255, 0.7)',
    'rgba(255, 159, 64, 0.7)',
    'rgba(46, 204, 113, 0.7)',
    'rgba(231, 76, 60, 0.7)',
    'rgba(142, 68, 173, 0.7)',
    'rgba(26, 188, 156, 0.7)',
    'rgba(241, 196, 15, 0.7)',
    'rgba(52, 73, 94, 0.7)'
];
$js_background_colors = json_encode($background_colors);

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - สรุปผลความพึงพอใจ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- ⭐️ 4. เพิ่มปลั๊กอินสำหรับแสดงข้อมูลบนกราฟ (Datalabels) -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card-header-custom {
            background-color: #17a2b8; /* Bootstrap info color */
            color: white;
        }
        /* ⭐️ สไตล์สำหรับคำอธิบายสัญลักษณ์ที่สร้างเอง */
        .custom-legend .legend-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        .custom-legend .legend-color-box {
            width: 20px;
            height: 20px;
            margin-right: 10px;
            border: 1px solid #ddd;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <?php
        // --- ส่วนแสดงผลของกราฟ ---
        // แต่ละไฟล์จะรับผิดชอบการแสดงผล Card และการสร้างกราฟของตัวเอง
        include 'graph/satisfaction_pie_chart.php';
        include 'graph/school_supervision_chart.php';
        include 'graph/position_supervision_chart.php';
        include 'graph/learning_group_chart.php';
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ⭐️ 5. ลงทะเบียนปลั๊กอิน Datalabels กับ Chart.js
        Chart.register(ChartDataLabels);

        // --- ส่วนของ JavaScript สำหรับสร้างกราฟ (ย้ายมาจากไฟล์ include) ---
        <?php
            // โค้ด JavaScript จากไฟล์ย่อยจะถูก include เข้ามาทำงานที่นี่
            // เพื่อให้แน่ใจว่า Chart.register() ทำงานก่อนการสร้างกราฟเสมอ
            include 'graph/satisfaction_pie_chart.js.php';
            include 'graph/school_supervision_chart.js.php';
            include 'graph/position_supervision_chart.js.php';
            include 'graph/learning_group_chart.js.php';
        ?>
    </script>
</body>
</html>
