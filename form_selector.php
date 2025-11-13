<?php
// ไฟล์: form_selector.php (HTML Fragment สำหรับรวมใน summary.php)
// ไฟล์นี้ไม่มี session_start() หรือ PHP Logic ใดๆ
?>
    <div class="card-body">
        <h5 class="mb-3 text-success">โปรดเลือกแบบฟอร์มสำหรับการประเมิน</h5>

        <div class="form-check mb-2">
            <input class="form-check-input" type="radio" name="evaluation_type" id="form1" value="kpi_form" required>
            <label class="form-check-label fw-bold" for="form1">
                แบบบันทึกการจัดการเรียนรู้และการจัดการชั้นเรียน
            </label>
        </div>

        <div class="form-check mb-4">
            <input class="form-check-input" type="radio" name="evaluation_type" id="form2" value="form_2" required>
            <label class="form-check-label fw-bold" for="form2">
                แบบกรอกข้อมูลนิทเทศตามนโยบาย
            </label>
        </div>
        
        </div>
</div>