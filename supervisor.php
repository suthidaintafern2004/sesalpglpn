<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>แบบบันทึกข้อมูลนิเทศ</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>

    <div class="main-card card">
        <div class="form-header card-header text-center">
            <i class="fas fa-file-alt"></i> <span class="fw-bold">แบบบันทึกข้อมูลผู้นิเทศ และ ผู้รับนิเทศ</span>
        </div>
        
        <form method="POST" action="summary.php"> 
        
            <div class="card-body">
                <h5 class="card-title fw-bold">ข้อมูลผู้นิเทศ</h5>
                <hr>
                <div class="row g-3">
                    
                    <div class="col-md-6">
                        <label for="supervisor_name" class="form-label fw-bold">ชื่อผู้นิเทศ</label>
                        <select id="supervisor_name" name="supervisor_name" class="form-select search-field" onchange="fetchPersonnelData()">
                            <option value="">-- กรุณาเลือกชื่อผู้นิเทศ --</option>
                            </select>
                    </div>

                    <div class="col-md-6">
                        <label for="p_id" class="form-label fw-bold">เลขบัตรประจำตัวประชาชน</label>
                        <input type="text" id="p_id" name="s_p_id" class="form-control display-field" placeholder="--" readonly>
                    </div>

                    <div class="col-md-6">
                        <label for="agency" class="form-label fw-bold">สังกัด</label>
                        <input type="text" id="agency" name="agency" class="form-control display-field" placeholder="--" readonly>
                    </div>

                    <div class="col-md-6">
                        <label for="position" class="form-label fw-bold">ตำแหน่ง</label>
                        <input type="text" id="position" name="position" class="form-control display-field" placeholder="--" readonly>
                    </div>
                </div>
            </div>
            <script>
    // ⭐️ โค้ด JavaScript ใน supervisor.php (ย้ายไปที่นี่เพื่อให้พร้อมใช้งาน)
    function populateNameDropdown() {
        const selectElement = document.getElementById('supervisor_name');
        
        // ตรวจสอบว่า selectElement มีอยู่จริงก่อนเรียก fetch
        if (!selectElement) return;

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
            .catch(error => console.error('Error fetching supervisor names:', error));
    }

    function fetchPersonnelData() {
        const selectedName = document.getElementById('supervisor_name').value; 
        const pidField = document.getElementById('p_id');
        const agencyField = document.getElementById('agency'); 
        const positionField = document.getElementById('position');

        pidField.value = '';
        agencyField.value = ''; 
        positionField.value = '';

        if (selectedName) {
            fetch(`fetch_supervisor.php?full_name=${encodeURIComponent(selectedName)}`) 
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        pidField.value = result.data.p_id;
                        agencyField.value = result.data.OfficeName; 
                        positionField.value = result.data.position;
                    } else {
                        console.error(result.message);
                        // alert('ไม่สามารถดึงข้อมูลบุคลากรได้: ' + result.message);
                    }
                })
                .catch(error => {
                    console.error('AJAX Error:', error);
                    // alert('เกิดข้อผิดพลาดในการเชื่อมต่อข้อมูล');
                });
        }
    }
</script>