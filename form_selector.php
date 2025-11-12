<?php
// ไฟล์: form_selector.php

// 1. ตรวจสอบว่ามีการกดปุ่ม "บันทึก" หรือไม่
if (isset($_POST['submit_selection'])) {
    
    // ******************************************************
    // *** แก้ไข: ตรวจสอบว่าค่าของ Radio Button ถูกส่งมาหรือไม่ ***
    // ******************************************************
    if (isset($_POST['evaluation_type'])) {
        
        // 2. รับค่าที่เลือกจาก Radio Button
        $selected_form = $_POST['evaluation_type'];
        
        // 3. กำหนดชื่อไฟล์ปลายทางตามค่าที่รับมา
        $target_page = '';
        
        if ($selected_form === 'kpi_form') {
            // ถ้าเลือก แบบบันทึกการจัดการเรียนรู้
            $target_page = 'kpi_form.php'; 
        } elseif ($selected_form === 'form_2') {
            // ถ้าเลือก แบบกรอกข้อมูลตามนโยบาย
            $target_page = 'form_2.php';
        }
        
        // 4. ทำการเปลี่ยนหน้า (Redirect) ไปยังไฟล์ปลายทาง
        if ($target_page !== '') {
            // header() ต้องถูกเรียกก่อนที่จะมีการแสดงผล (output) ใดๆ ออกมา
            header("Location: " . $target_page);
            exit(); // หยุดการทำงานของสคริปต์
        } else {
            // กรณีที่มีการเลือก แต่ค่า value ไม่ตรงกับที่กำหนด (kpi_form หรือ form_2)
            $error_message = "ค่าแบบฟอร์มที่เลือกไม่ถูกต้อง โปรดลองใหม่อีกครั้ง";
        }
        
    } else {
        // กรณีที่ไม่มีการเลือกเลย
        $error_message = "กรุณาเลือกแบบฟอร์มสำหรับการประเมินก่อนบันทึก";
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เลือกแบบฟอร์ม</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background-color: #feee91; padding: 50px;">
    <div class="container" style="max-width: 600px; background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
        
        <?php 
        // แสดงข้อความเตือนถ้ามี
        if (isset($error_message)) {
            echo '<div class="alert alert-danger" role="alert">' . $error_message . '</div>';
        }
        ?>
        
        <form method="POST" action="form_selector.php">
            <h5 class="mb-4">โปรดเลือกแบบฟอร์มสำหรับการประเมิน</h5>

            <div class="form-check mb-2">
                <input class="form-check-input" type="radio" name="evaluation_type" id="form1" value="kpi_form">
                <label class="form-check-label" for="form1">
                    แบบบันทึกการจัดการเรียนรู้และการจัดการชั้นเรียน
                </label>
            </div>

            <div class="form-check mb-4">
                <input class="form-check-input" type="radio" name="evaluation_type" id="form2" value="form_2" checked>
                <label class="form-check-label" for="form2">
                    แบบกรอกข้อมูลนิทเทศตามนโยบายและจุดเน้นของสำนักงานเขตพื้นที่การศึกษา
                </label>
            </div>

            <button type="submit" name="submit_selection" class="btn btn-success" style="background-color: #28a745;">
                บันทึก
            </button>
        </form>
    </div>
</body>
</html>