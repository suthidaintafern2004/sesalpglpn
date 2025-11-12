<?php 
// teacher_form.php
// ดึงรายชื่อครูสำหรับ Datalist (วิธี PHP ดั้งเดิม)
$sql_teachers = "SELECT CONCAT(IFNULL(PrefixName,''), ' ', Fname, ' ', Lname) AS full_name_display FROM teacher ORDER BY Fname ASC";
$result_teachers = $conn->query($sql_teachers);
?>
<hr>
<div class="card-body">
    <div class="row g-3">
        
        <div class="col-md-6">
            <label for="teacher_name_input" class="form-label fw-bold">ชื่อผู้รับนิเทศ</label>
            <input list="teacher_names_list" id="teacher_name_input" name="teacher_name"
                class="form-control search-field"
                placeholder="-- พิมพ์เพื่อค้นหา --"
                onchange="fetchTeacherData(this.value)"> 
            
            <datalist id="teacher_names_list">
                <?php
                if ($result_teachers) {
                    while ($row_teacher = $result_teachers->fetch_assoc()) {
                        echo '<option value="' . htmlspecialchars(trim($row_teacher['full_name_display'])) . '">';
                    }
                }
                ?>
            </datalist>
        </div>
        
        <div class="col-md-6">
            <label for="t_pid" class="form-label fw-bold">เลขบัตรประจำตัวประชาชน</label>
            <input type="text" id="t_pid" class="form-control display-field" placeholder="--" readonly>
        </div>

        <div class="col-md-6">
            <label for="adm_name" class="form-label fw-bold">วิทยฐานะ</label>
            <input type="text" id="adm_name" class="form-control display-field" placeholder="--" readonly>
        </div>

        <div class="col-md-6">
            <label for="learning_group" class="form-label fw-bold">กลุ่มสาระการเรียนรู้</label>
            <input type="text" id="learning_group" class="form-control display-field" placeholder="--" readonly>
        </div>
    </div>
</div>

<script>
// ฟังก์ชันสำหรับดึงข้อมูลผู้รับนิเทศเมื่อมีการเลือกชื่อ
function fetchTeacherData(selectedName) {
    // กำหนด ID ของช่องแสดงผล
    const tidField = document.getElementById('t_pid');
    const admNameField = document.getElementById('adm_name'); 
    const learningGroupField = document.getElementById('learning_group');

    // เคลียร์ข้อมูลเก่า
    tidField.value = ''; 
    admNameField.value = ''; 
    learningGroupField.value = '';

    if (selectedName) {
        // ⭐️ AJAX เรียกใช้ fetch_teacher.php โดยส่งชื่อเต็ม
        fetch(`fetch_teacher.php?full_name=${encodeURIComponent(selectedName)}`) 
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    // เติมข้อมูลลงในกรอบสีเหลือง โดยใช้คีย์ข้อมูลที่ถูกต้อง
                    tidField.value = result.data.t_pid; // ⭐️ ใช้ t_pid
                    admNameField.value = result.data.adm_name; 
                    learningGroupField.value = result.data.learning_group; 
                } else {
                    console.error(result.message);
                    alert('ไม่สามารถดึงข้อมูลครูได้: ' + result.message);
                }
            })
            .catch(error => {
                console.error('AJAX Error:', error);
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อข้อมูล');
            });
    }
}
// หากไฟล์นี้ถูกนำเข้าในหน้าหลัก (index.php) ที่มีการเรียก window.onload อยู่แล้ว
// คุณไม่จำเป็นต้องมี window.onload ในไฟล์นี้

</script>