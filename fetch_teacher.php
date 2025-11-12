<?php
// fetch_teacher.php

header('Content-Type: application/json');
require_once 'db_connect.php'; // ใช้ require_once สำหรับไฟล์เชื่อมต่อ DB

// ----------------------------------------------------------------------
// โหมด 1: ดึงข้อมูลเฉพาะบุคคลเมื่อเลือกชื่อ (full_name)
// ----------------------------------------------------------------------
if (isset($_GET['full_name'])) {
    $full_name = $_GET['full_name'];
    $full_name_parts = explode(' ', $full_name);
    
    // ⭐️ ใช้ Fname และ Lname ในการค้นหาจากชื่อเต็ม (สมมติว่าชื่อเต็มคือ PrefixName Fname Lname)
    // ใช้ตำแหน่งที่ 1 (ชื่อ) และสุดท้าย (นามสกุล) ในการค้นหา
    $teacher_fname = $conn->real_escape_string($full_name_parts[1]); 
    $teacher_lname = $conn->real_escape_string(end($full_name_parts)); 
    
    // ⭐️ SQL: ใช้ t_pid, adm_name, learning_group (ชื่อคอลัมน์จากตาราง teacher)
    $sql = "SELECT t_pid, adm_name, learning_group FROM teacher 
            WHERE Fname = '$teacher_fname' AND Lname = '$teacher_lname'";
            
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // ⭐️ ส่งคีย์ที่ตรงกับ ID ใน HTML (t_pid, adm_name, learning_group)
        echo json_encode(['success' => true, 'data' => [
            't_pid' => $row['t_pid'], 
            'adm_name' => $row['adm_name'], 
            'learning_group' => $row['learning_group']
        ]]);
    } else {
        echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลครูคนนี้']);
    }

// ----------------------------------------------------------------------
// โหมด 2: ดึงรายชื่อเต็มสำหรับ Datalist (action=get_names)
// ----------------------------------------------------------------------
} else if (isset($_GET['action']) && $_GET['action'] == 'get_names') {
    // ⭐️ ใช้ CONCAT() เพื่อรวมคำนำหน้า ชื่อ และนามสกุล
    $sql_names = "SELECT CONCAT(IFNULL(PrefixName, ''), ' ', Fname, ' ', Lname) AS full_name_display 
                  FROM teacher 
                  ORDER BY Fname ASC"; 
    
    $result_names = $conn->query($sql_names);
    
    $names = [];
    if ($result_names) {
        while ($row = $result_names->fetch_assoc()) {
            $names[] = trim($row['full_name_display']); 
        }
    }
    echo json_encode($names);

} else {
    echo json_encode(['success' => false, 'message' => 'ไม่มีพารามิเตอร์การค้นหา']);
}

$conn->close();
?>