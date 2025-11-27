<?php
// ไฟล์: summary.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db_connect.php';

// ฟังก์ชันสำหรับดึงชื่อจาก PID (ป้องกันโค้ดซ้ำซ้อน)
function getPersonName($conn, $pid, $type) {
    if ($type === 'supervisor') {
        $stmt = $conn->prepare("SELECT CONCAT(IFNULL(PrefixName, ''), Fname, ' ', Lname) AS full_name FROM supervisor WHERE p_id = ?");
    } else { // teacher
        $stmt = $conn->prepare("SELECT CONCAT(IFNULL(PrefixName, ''), Fname, ' ', Lname) AS full_name FROM teacher WHERE t_pid = ?");
    }
    
    if ($stmt) {
        $stmt->bind_param("s", $pid);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            return $result->fetch_assoc()['full_name'];
        }
        $stmt->close();
    }
    return 'ไม่พบข้อมูล';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับค่า supervisor_pid, teacher_pid, และ evaluation_type จากฟอร์ม
    $supervisor_pid = $_POST['supervisor_pid'] ?? null;
    $teacher_pid = $_POST['teacher_pid'] ?? null;
    $evaluation_type = $_POST['evaluation_type'] ?? null;

    if (!$supervisor_pid || !$teacher_pid || !$evaluation_type) {
        // ถ้าข้อมูลไม่ครบ ให้กลับไปหน้าแรกพร้อมข้อความ error
        $_SESSION['flash_message'] = "เกิดข้อผิดพลาด: กรุณาเลือกข้อมูลให้ครบถ้วน";
        header('Location: index.php');
        exit();
    }

    // ดึงชื่อจากฐานข้อมูล
    $supervisor_name = getPersonName($conn, $supervisor_pid, 'supervisor');
    $teacher_name = getPersonName($conn, $teacher_pid, 'teacher');

    // เก็บข้อมูลลงใน Session
    $_SESSION['inspection_data'] = [
        'supervisor_pid' => $supervisor_pid,
        'supervisor_name' => $supervisor_name,
        'teacher_pid' => $teacher_pid,
        'teacher_name' => $teacher_name,
        'evaluation_type' => $evaluation_type
    ];

    $conn->close();

    // ⭐️ แก้ไข: ส่งต่อไปยังฟอร์มที่เลือกด้วย header('Location: ...') ที่ถูกต้อง
    if ($evaluation_type === 'kpi_form') {
        header('Location: forms/kpi_form.php');
        exit();
    } elseif ($evaluation_type === 'quickwin_form') {
        header('Location: forms/quickwin_form.php');
        exit();
    } else {
        $_SESSION['flash_message'] = "เกิดข้อผิดพลาด: ประเภทแบบฟอร์มไม่ถูกต้อง";
        header('Location: index.php');
        exit();
    }
} else {
    // ถ้าไม่ได้เข้ามาหน้านี้ด้วย POST ให้กลับไปหน้าแรก
    header('Location: index.php');
    exit();
}