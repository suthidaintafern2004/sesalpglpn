<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>แบบบันทึกข้อมูลนิเทศ</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="styles.css">

</head>

<body>

    <div class="main-card card">
        <div class="form-header card-header text-center">
            <i class="fas fa-file-alt"></i> <span class="fw-bold">แบบบันทึกข้อมูลนิเทศ</span>
        </div>

        <div class="card-body">
            <div class="row g-3">
                
                <div class="col-md-6">
                    <label for="supervisor_name" class="form-label fw-bold">ชื่อผู้นิเทศ</label>
                    <select id="supervisor_name" class="form-select search-field" onchange="fetchPersonnelData()">
                        <option value="">-- กรุณาเลือกชื่อผู้นิเทศ --</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="p_id" class="form-label fw-bold">เลขบัตรประจำตัวประชาชน</label>
                    <input type="text" id="p_id" class="form-control display-field" placeholder="--" readonly>
                </div>

                <div class="col-md-6">
                    <label for="agency" class="form-label fw-bold">สังกัด</label>
                    <input type="text" id="agency" class="form-control display-field" placeholder="--" readonly>
                </div>

                <div class="col-md-6">
                    <label for="position" class="form-label fw-bold">ตำแหน่ง</label>
                    <input type="text" id="position" class="form-control display-field" placeholder="--" readonly>
                </div>
            </div>
        </div>

    <script>
    // ... โค้ด JavaScript เดิม (ไม่จำเป็นต้องแก้ไข) ...
    function populateNameDropdown() {
        // ... (โค้ดเดิม)
        const selectElement = document.getElementById('supervisor_name');
        
        fetch('fetch_supervisor.php?action=get_names')
            .then(response => response.json())
            .then(names => {
                names.forEach(name => {
                    const option = document.createElement('option');
                    option.value = name; 
                    option.textContent = name;
                    selectElement.appendChild(option);
                });
            })
            .catch(error => console.error('Error fetching names:', error));
    }

    function fetchPersonnelData() {
        // ... (โค้ดเดิม)
        const selectedName = document.getElementById('supervisor_name').value; 
        const pidField = document.getElementById('p_id');
        const agencyField = document.getElementById('agency'); 
        const positionField = document.getElementById('position');

        // เคลียร์ข้อมูลเก่าในกรอบสีเหลือง
        pidField.value = '';
        agencyField.value = ''; 
        positionField.value = '';

        if (selectedName) {
            fetch(`fetch_supervisor.php?full_name=${encodeURIComponent(selectedName)}`) 
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        // เติมข้อมูลลงในกรอบสีเหลือง
                        pidField.value = result.data.p_id;
                        agencyField.value = result.data.OfficeName; 
                        positionField.value = result.data.position;
                    } else {
                        console.error(result.message);
                        alert('ไม่สามารถดึงข้อมูลบุคลากรได้: ' + result.message);
                    }
                })
                .catch(error => {
                    console.error('AJAX Error:', error);
                    alert('เกิดข้อผิดพลาดในการเชื่อมต่อข้อมูล');
                });
        }
    }

    window.onload = populateNameDropdown;
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>