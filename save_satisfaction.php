<?php
// ไฟล์: save_satisfaction.php
session_start();
require_once 'db_connect.php';

function redirect_with_error($message) {
    // สามารถสร้างหน้าแสดงข้อผิดพลาดที่สวยงามได้ในอนาคต
    die("เกิดข้อผิดพลาด: " . htmlspecialchars($message));
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirect_with_error("Invalid request method.");
}

if (!isset($_SESSION['satisfaction_data']['session_id'])) {
    redirect_with_error("Session หมดอายุหรือไม่พบข้อมูลการนิเทศ กรุณาเริ่มต้นใหม่");
}

$session_id = $_SESSION['satisfaction_data']['session_id'];
$ratings = $_POST['ratings'] ?? [];
$overall_suggestion = trim($_POST['overall_suggestion'] ?? '');

if (empty($ratings)) {
    redirect_with_error("กรุณาให้คะแนนความพึงพอใจอย่างน้อยหนึ่งข้อ");
}

// เริ่มต้น Transaction
$conn->begin_transaction();

try {
    // 1. บันทึกคะแนนแต่ละข้อ
    $sql_answer = "INSERT INTO satisfaction_answers (session_id, question_id, rating) VALUES (?, ?, ?)";
    $stmt_answer = $conn->prepare($sql_answer);

    foreach ($ratings as $question_id => $rating) {
        $q_id = (int)$question_id;
        $rate_score = (int)$rating;
        $stmt_answer->bind_param("iii", $session_id, $q_id, $rate_score);
        $stmt_answer->execute();
    }
    $stmt_answer->close();

    // 2. อัปเดตตาราง supervision_sessions เพื่อเก็บข้อเสนอแนะ และสถานะการประเมิน
    // (เพิ่มคอลัมน์ satisfaction_suggestion และ satisfaction_submitted)
    $sql_session_update = "UPDATE supervision_sessions 
                           SET satisfaction_suggestion = ?, 
                               satisfaction_submitted = 1,
                               satisfaction_date = NOW()
                           WHERE id = ?";
    $stmt_session = $conn->prepare($sql_session_update);
    $stmt_session->bind_param("si", $overall_suggestion, $session_id);
    $stmt_session->execute();
    $stmt_session->close();

    // ยืนยัน Transaction
    $conn->commit();

    // ล้าง session และเปลี่ยนเส้นทาง
    unset($_SESSION['satisfaction_data']);
    
    // ส่งกลับไปหน้าประวัติของครูคนเดิม
    header("Location: history.php");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    redirect_with_error("ไม่สามารถบันทึกข้อมูลได้: " . $e->getMessage());
}

$conn->close();
?>