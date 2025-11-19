<?php
// ไฟล์: supervision_report.php
require_once 'db_connect.php';

// ตรวจสอบค่า session_id
// ⭐️ แก้ไข: เปลี่ยนจาก $_GET เป็น $_POST ⭐️
if (!isset($_POST['session_id']) || empty($_POST['session_id'])) {
    die("ไม่พบรหัสการนิเทศ");
}

$session_id = intval($_POST['session_id']); // ⭐️ แก้ไข: เปลี่ยนจาก $_GET เป็น $_POST ⭐️

// 1. ดึงข้อมูลการนิเทศ (Supervision Info + Teacher + Supervisor)
// ใช้ JOIN เพื่อดึงข้อมูลจากหลายตารางพร้อมกัน
$sql_info = "SELECT 
                ss.*,
                /* ข้อมูลครู */
                t.PrefixName AS t_prefix, t.fname AS t_fname, t.lname AS t_lname, 
                t.t_pid, t.adm_name AS t_position, t.learning_group,
                s_school.SchoolName AS t_school,
                /* ข้อมูลผู้นิเทศ */
                sp.PrefixName AS s_prefix, sp.fname AS s_fname, sp.lname AS s_lname, 
                sp.p_id AS s_pid, sp.RankName AS s_rank, sp.OfficeName AS s_office
            FROM supervision_sessions ss
            LEFT JOIN teacher t ON ss.teacher_t_pid = t.t_pid
            LEFT JOIN school s_school ON t.school_id = s_school.school_id
            LEFT JOIN supervisor sp ON ss.supervisor_p_id = sp.p_id
            WHERE ss.id = ?";

$stmt = $conn->prepare($sql_info);
$stmt->bind_param("i", $session_id);
$stmt->execute();
$result_info = $stmt->get_result();
$info = $result_info->fetch_assoc();

if (!$info) {
    die("ไม่พบข้อมูลการนิเทศสำหรับรหัสนี้");
}

// 2. ดึงคะแนนและข้อค้นพบ (KPI Answers)
$sql_answers = "SELECT 
                    q.question_text, 
                    ans.rating_score, 
                    ans.comment,
                    ind.title AS indicator_title,
                    ind.id AS indicator_id
                FROM kpi_answers ans
                JOIN kpi_questions q ON ans.question_id = q.id
                JOIN kpi_indicators ind ON q.indicator_id = ind.id
                WHERE ans.session_id = ?
                ORDER BY ind.display_order, q.display_order";

$stmt_ans = $conn->prepare($sql_answers);
$stmt_ans->bind_param("i", $session_id);
$stmt_ans->execute();
$result_ans = $stmt_ans->get_result();

// จัดกลุ่มข้อมูลตามตัวชี้วัด
$kpi_data = [];
$total_score = 0;
$count_questions = 0;

while ($row = $result_ans->fetch_assoc()) {
    $kpi_data[$row['indicator_id']]['title'] = $row['indicator_title'];
    $kpi_data[$row['indicator_id']]['questions'][] = $row;

    $total_score += $row['rating_score'];
    $count_questions++;
}

// 3. ดึงข้อเสนอแนะเพิ่มเติม (Suggestions)
$sql_sugg = "SELECT indicator_id, suggestion_text FROM kpi_indicator_suggestions WHERE session_id = ?";
$stmt_sugg = $conn->prepare($sql_sugg);
$stmt_sugg->bind_param("i", $session_id);
$stmt_sugg->execute();
$result_sugg = $stmt_sugg->get_result();

$suggestions = [];
while ($row = $result_sugg->fetch_assoc()) {
    $suggestions[$row['indicator_id']] = $row['suggestion_text'];
}

?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>รายงานผลการนิเทศ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Sarabun', sans-serif;
        }

        .report-container {
            max-width: 900px;
            margin: 30px auto;
            background: #fff;
            padding: 40px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .header-title {
            color: #0d6efd;
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .info-box {
            background-color: #eef6ff;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #cce5ff;
        }

        .table-kpi th {
            background-color: #f8f9fa;
        }

        .score-badge {
            font-size: 0.9rem;
            padding: 5px 10px;
            border-radius: 20px;
        }

        .badge-3 {
            background-color: #198754;
            color: white;
        }

        /* ดีมาก */
        .badge-2 {
            background-color: #ffc107;
            color: black;
        }

        /* พอใช้ */
        .badge-1 {
            background-color: #e83e8c; /* Pink */
            color: white;
        }

        /* ต้องปรับปรุง */
        .badge-0 {
            background-color: #dc3545;
            color: white;
        }

        /* ปรับปรุง */
        
        @media print {
            .no-print {
                display: none;
            }

            .report-container {
                box-shadow: none;
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="report-container">

            <div class="text-center mb-4">
                <h3>รายงานผลการนิเทศการสอน</h3>
                <p class="text-muted">ระบบสารสนเทศเพื่อการนิเทศ (SESA)</p>
            </div>

            <h5 class="header-title"><i class="fas fa-user-tie"></i> ข้อมูลผู้รับนิเทศ</h5>
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>ชื่อ-นามสกุล:</strong> <?php echo $info['t_prefix'] . $info['t_fname'] . ' ' . $info['t_lname']; ?>
                </div>
                <div class="col-md-6">
                    <strong>สังกัด (โรงเรียน):</strong> <?php echo $info['t_school']; ?>
                </div>
                <div class="col-md-6">
                    <strong>ตำแหน่ง/วิทยฐานะ:</strong> <?php echo $info['t_position']; ?>
                </div>
                <div class="col-md-6">
                    <strong>กลุ่มสาระการเรียนรู้:</strong> <?php echo $info['learning_group'] ?? '-'; ?>
                </div>
            </div>

            <h5 class="header-title"><i class="fas fa-user-check"></i> ข้อมูลผู้นิเทศ</h5>
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>ชื่อ-นามสกุล:</strong> <?php echo $info['s_prefix'] . $info['s_fname'] . ' ' . $info['s_lname']; ?>
                </div>
                <div class="col-md-6">
                    <strong>วิทยฐานะ/ตำแหน่ง:</strong> <?php echo $info['s_rank']; ?> (<?php echo $info['s_office']; ?>)
                </div>
            </div>

            <h5 class="header-title"><i class="fas fa-clipboard-list"></i> ข้อมูลการนิเทศ</h5>
            <div class="info-box">
                <div class="row">
                    <div class="col-md-3"><strong>รหัสวิชา:</strong> <?php echo $info['subject_code']; ?></div>
                    <div class="col-md-4"><strong>ชื่อวิชา:</strong> <?php echo $info['subject_name']; ?></div>
                    <div class="col-md-2"><strong>ครั้งที่นิเทศ:</strong> <?php echo $info['inspection_time']; ?></div>
                    <div class="col-md-3"><strong>วันที่:</strong> <?php echo date('d/m/Y', strtotime($info['inspection_date'])); ?></div>
                </div>
            </div>

            <h5 class="header-title mt-4"><i class="fas fa-star"></i> ผลการประเมินตามตัวชี้วัด (KPI)</h5>

            <div class="table-responsive">
                <table class="table table-bordered table-kpi">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40%;">ประเด็นคำถาม</th>
                            <th style="width: 10%; text-align: center;">คะแนน</th>
                            <th style="width: 50%;">ข้อค้นพบ / ความคิดเห็น</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kpi_data as $ind_id => $data): ?>
                            <tr class="table-secondary">
                                <td colspan="3" class="fw-bold"><?php echo $data['title']; ?></td>
                            </tr>

                            <?php foreach ($data['questions'] as $q): ?>
                                <tr>
                                    <td><?php echo $q['question_text']; ?></td>
                                    <td class="text-center">
                                        <?php
                                        $score = $q['rating_score'];
                                        $class = 'badge-2'; // พอใช้ (สีเหลือง)
                                        if ($score == 3) $class = 'badge-3';      // ดีมาก (สีเขียว)
                                        elseif ($score == 1) $class = 'badge-1';  // ปรับปรุง (สีชมพู)
                                        elseif ($score == 0) $class = 'badge-0';  // ต้องปรับปรุง (สีแดง)
                                        $class = 'badge-2'; // Default to พอใช้ (สีเหลือง) for score 2
                                        if ($score == 3) $class = 'badge-3'; // ดีมาก (สีเขียว)
                                        elseif ($score == 1) $class = 'badge-1'; // พอใช้ (สีชมพู)
                                        elseif ($score == 0) $class = 'badge-0'; // ปรับปรุง (สีแดง)
                                        ?>
                                        <span class="badge score-badge <?php echo $class; ?>"><?php echo htmlspecialchars($score); ?></span>
                                    </td>
                                    <td><?php echo !empty($q['comment']) ? nl2br(htmlspecialchars($q['comment'])) : '-'; ?></td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (isset($suggestions[$ind_id]) && !empty($suggestions[$ind_id])): ?>
                                <tr>
                                    <td colspan="3" class="bg-light text-primary">
                                        <i class="fas fa-comment-dots"></i> <strong>ข้อเสนอแนะเพิ่มเติม:</strong>
                                        <?php echo nl2br(htmlspecialchars($suggestions[$ind_id])); ?>
                                    </td>
                                </tr>
                            <?php endif; ?>

                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-dark">
                            <td class="text-end"><strong>คะแนนรวมทั้งหมด</strong></td>
                            <td class="text-center fw-bold"><?php echo $total_score; ?> / <?php echo $count_questions * 3; ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <?php if (!empty($info['overall_suggestion'])): ?>
            <div class="card mt-4 border-info">
                <div class="card-header bg-info text-dark fw-bold">
                    <i class="fas fa-lightbulb"></i> ข้อเสนอแนะเพิ่มเติม
                </div>
                <div class="card-body">
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($info['overall_suggestion'])); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <div class="text-center mt-5 no-print">
                <a href="history.php" class="btn btn-secondary me-2"><i class="fas fa-list-alt"></i> กลับไปหน้าประวัติ</a>
                <button onclick="window.print()" class="btn btn-secondary"><i class="fas fa-print"></i> พิมพ์รายงาน</button>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>