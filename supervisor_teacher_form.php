// ในส่วน JavaScript/AJAX

// ... โค้ดส่วนบน (document.addEventListener('DOMContentLoaded', ...))
    
    const teacherInput = document.getElementById('teacher_name_input');
    const teacherList = document.getElementById('teacher_names_list');

    // Event Listener สำหรับช่องค้นหาผู้รับนิเทศ
    teacherInput.addEventListener('input', function(e) {
        // ⭐️ แก้ไข: trim() ค่าที่ป้อนเข้ามาเพื่อลบช่องว่างนำหน้า/ตามหลัง
        const inputValue = e.target.value.trim(); 
        let selectedPid = null;
        let matchFound = false;

        // วนลูปหา option ที่ตรงกับค่าที่ป้อน
        for (const option of teacherList.options) {
            // ⭐️ แก้ไข: trim() ค่า option.value ด้วย
            if (option.value.trim() === inputValue) { 
                selectedPid = option.getAttribute('data-pid');
                matchFound = true;
                break;
            }
        }

        // ถ้าเจอข้อมูลที่ตรงกัน ให้ดึงข้อมูล
        if (matchFound && selectedPid) { // ⭐️ ตรวจสอบว่ามี selectedPid ด้วย
            fetchTeacherData(selectedPid);
        } else {
            // ถ้าไม่ตรง หรือ selectedPid เป็น null ให้ล้างข้อมูลที่แสดงอยู่ 
            clearTeacherDataFields();
        }
    });

// ... โค้ดส่วนที่เหลือ