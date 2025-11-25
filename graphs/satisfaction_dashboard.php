<?php
// ไฟล์: satisfaction_dashboard.php
require_once '../config/db_connect.php';

// --- 1. การดึงข้อมูลสำหรับ "แบบประเมินตนเอง" (Form 1) ---
// ใช้สำหรับกราฟวงกลมและเป็นข้อมูลชุดแรกของกราฟเปรียบเทียบ
$sql = "SELECT
            q.id AS question_id,
            q.question_text,
            AVG(ans.rating) AS average_score,
            COUNT(ans.id) AS response_count
        FROM
            satisfaction_questions q -- ตารางคำถาม
        LEFT JOIN
            satisfaction_answers ans ON q.id = ans.question_id -- ตารางคำตอบแบบประเมินตนเอง
        GROUP BY
            q.id, q.question_text
        ORDER BY
            q.display_order ASC";

$result = $conn->query($sql);

$form1_data = []; // เปลี่ยนชื่อตัวแปรเพื่อความชัดเจน
if ($result && $result->num_rows > 0) {
    $item_number = 1;
    while ($row = $result->fetch_assoc()) {
        $row['question_text_with_number'] = $item_number . '. ' . $row['question_text'];
        $form1_data[] = $row;
        $item_number++;
    }
}

// --- 2. การดึงข้อมูลสำหรับ "แบบประเมินโดยผู้บังคับบัญชา" (Form 2) ---
// (สมมติว่ามีตาราง supervisor_satisfaction_answers สำหรับเก็บข้อมูลส่วนนี้)
$sql_form2 = "SELECT
                q.id AS question_id,
                AVG(ans.rating) AS average_score
            FROM
                satisfaction_questions q
            LEFT JOIN
                supervisor_satisfaction_answers ans ON q.id = ans.question_id -- ตารางคำตอบของผู้บังคับบัญชา
            GROUP BY
                q.id
            ORDER BY
                q.display_order ASC";

// ดักจับ error กรณีตาราง supervisor_satisfaction_answers ไม่มีอยู่จริง
try {
    $result_form2 = $conn->query($sql_form2);
} catch (mysqli_sql_exception $e) {
    // หากเกิด exception (เช่น ตารางไม่มี) ให้กำหนด $result_form2 เป็น false
    // เพื่อให้โค้ดส่วนที่เหลือทำงานต่อไปได้โดยไม่แสดงผลข้อมูลส่วนนี้
    $result_form2 = false;
}

$form2_data = [];
if ($result_form2 && $result_form2->num_rows > 0) {
    // จัดข้อมูลใส่ array โดยใช้ question_id เป็น key เพื่อให้ง่ายต่อการจับคู่
    while ($row = $result_form2->fetch_assoc()) {
        $form2_data[$row['question_id']] = $row;
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

// --- ส่วนดึงข้อมูลสรุปจำนวนครูที่ได้รับการนิเทศตามกลุ่มสาระ (แก้ไขตาม request) ---
$sql_lg_supervised_teachers = "SELECT
                                    t.learning_group,
                                    COUNT(DISTINCT ss.teacher_t_pid) AS supervised_teacher_count
                                FROM
                                    supervision_sessions ss
                                JOIN
                                    teacher t ON ss.teacher_t_pid = t.t_pid
                                WHERE
                                    t.learning_group IS NOT NULL AND t.learning_group != ''
                                GROUP BY
                                    t.learning_group
                                ORDER BY
                                    supervised_teacher_count DESC";

$result_lg = $conn->query($sql_lg_supervised_teachers);
$lg_supervised_teacher_data = [];
if ($result_lg && $result_lg->num_rows > 0) {
    $lg_supervised_teacher_data = $result_lg->fetch_all(MYSQLI_ASSOC);
}

$conn->close();


// เตรียมข้อมูลสำหรับ Chart.js
// สำหรับกราฟวงกลม (ใช้ข้อมูล Form 1)
$chart_labels = json_encode(array_column($form1_data, 'question_text_with_number'));

// FIX: จัดการกับค่า NULL ที่อาจเกิดขึ้นจาก AVG() เมื่อไม่มีข้อมูล
// แปลงค่า NULL ทั้งหมดใน array ให้เป็น 0 เพื่อให้ Chart.js ทำงานได้
$scores = array_map(function($score) {
    return $score ?? 0; // ถ้า $score เป็น NULL ให้ใช้ 0 แทน
}, array_column($form1_data, 'average_score'));

$chart_values = json_encode($scores);

// เตรียมข้อมูลสำหรับกราฟเปรียบเทียบ (ใช้ข้อมูล Form 1 และ Form 2)
$comparison_chart_labels = json_encode(array_column($form1_data, 'question_text_with_number'));
$form1_scores_js = json_encode(array_column($form1_data, 'average_score'));
// สร้าง array คะแนนของ form 2 ให้มีลำดับตรงกับ form 1
$form2_scores = array_map(function($question) use ($form2_data) {
    return $form2_data[$question['question_id']]['average_score'] ?? 0;
}, $form1_data);
$form2_scores_js = json_encode($form2_scores);

// เตรียมข้อมูลสำหรับกราฟสรุปการนิเทศแต่ละโรงเรียน (เพิ่มใหม่)
$school_chart_labels = json_encode(array_column($school_supervision_data, 'SchoolName'));
$school_chart_values = json_encode(array_column($school_supervision_data, 'supervision_count'));

// เตรียมข้อมูลสำหรับกราฟสรุปตามตำแหน่ง (เพิ่มใหม่)
$position_chart_labels = json_encode(array_column($position_supervision_data, 'teacher_position'));
$position_chart_values = json_encode(array_column($position_supervision_data, 'supervised_teacher_count'));

// เตรียมข้อมูลสำหรับกราฟสรุปจำนวนครูที่ได้รับการนิเทศตามกลุ่มสาระ (แก้ไขตาม request)
$lg_chart_labels = json_encode(array_column($lg_supervised_teacher_data, 'learning_group'));
$lg_chart_values = json_encode(array_column($lg_supervised_teacher_data, 'supervised_teacher_count'));

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
    <title>Dashboard</title>
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
        // เปลี่ยนชื่อตัวแปร $dashboard_data เป็น $form1_data ก่อน include
        $dashboard_data = $form1_data;
        $lg_supervision_data = $lg_supervised_teacher_data; // ใช้ตัวแปรกลางสำหรับ include file เดิม
        include 'satisfaction_pie_chart.php'; // กราฟสรุปผลความพึงพอใจ (วงกลม)
        // include 'comparison_bar_chart.php'; // กราฟเปรียบเทียบ 2 แบบฟอร์ม (แท่ง)
        include 'learning_group_chart.php';
        include 'school_supervision_chart.php';
        include 'position_supervision_chart.php';
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ลงทะเบียนปลั๊กอิน Datalabels กับ Chart.js ให้ทำงานกับทุกกราฟ
        Chart.register(ChartDataLabels);
    </script>
</body>
</html>
