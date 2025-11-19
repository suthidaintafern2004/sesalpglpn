<?php
// 1. เชื่อมต่อฐานข้อมูล
require_once 'db_connect.php';

// 2. ดึงข้อมูลตัวชี้วัดและคำถามทั้งหมดในครั้งเดียวด้วย JOIN
$sql = "SELECT 
            ind.id AS indicator_id, 
            ind.title AS indicator_title,
            q.id AS question_id,
            q.question_text
        FROM 
            kpi_indicators ind
        LEFT JOIN 
            kpi_questions q ON ind.id = q.indicator_id
        ORDER BY 
            ind.display_order ASC, q.display_order ASC";

$result = $conn->query($sql);
// test
// 3. จัดกลุ่มข้อมูลให้อยู่ในรูปแบบที่ใช้งานง่าย
$indicators = [];
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $indicators[$row['indicator_id']]['title'] = $row['indicator_title'];
    if ($row['question_id']) { // ตรวจสอบว่ามีคำถามหรือไม่
      $indicators[$row['indicator_id']]['questions'][] = $row;
    }
  }
}

// ดึงข้อมูลจาก Session มาใช้
$inspection_data = $_SESSION['inspection_data'] ?? [];
?>
<!-- ไม่ต้องมี <html> <head> <body> เพราะไฟล์นี้จะถูก include -->

<!-- แบบฟอร์มหลักที่รวมทุกอย่าง -->
<form id="evaluationForm" method="POST" action="save_kpi_data.php" onsubmit="return validateKpiForm()">

  <!-- ================================================== -->
  <!-- ===== ส่วนแสดงข้อมูลและกรอกข้อมูลการนิเทศ (ย้ายมาที่นี่) ===== -->
  <!-- ================================================== -->
  <h4 class="fw-bold text-primary">ข้อมูลผู้นิเทศ</h4>
  <div class="row mb-4">
    <div class="col-md-6">
      <strong>ชื่อผู้นิเทศ:</strong> <?php echo htmlspecialchars($inspection_data['supervisor_name'] ?? 'ไม่มีข้อมูล'); ?>
    </div>
    <div class="col-md-6">
      <strong>ผู้รับการนิเทศ:</strong> <?php echo htmlspecialchars($inspection_data['teacher_name'] ?? 'ไม่มีข้อมูล'); ?>
    </div>
  </div>

  <hr class="my-4">

  <h4 class="fw-bold text-success">กรอกข้อมูลการนิเทศ</h4>
  <div class="row g-3 mt-2 mb-4">
    <div class="col-md-6">
      <label for="subject_code" class="form-label fw-bold">รหัสวิชา</label>
      <input type="text" id="subject_code" name="subject_code" class="form-control" placeholder="เช่น ท0001" required>
    </div>
    <div class="col-md-6">
      <label for="subject_name" class="form-label fw-bold">ชื่อวิชา</label>
      <input type="text" id="subject_name" name="subject_name" class="form-control" placeholder="เช่น ภาษาไทย" required>
    </div>
    <div class="col-md-6">
      <label for="inspection_time" class="form-label fw-bold">ครั้งที่นิเทศ</label>
      <select id="inspection_time" name="inspection_time" class="form-select" required>
        <option value="" disabled selected>-- เลือกครั้งที่นิเทศ --</option>
        <?php for ($i = 1; $i <= 9; $i++): ?>
          <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
        <?php endfor; ?>
      </select>
    </div>
    <div class="col-md-6">
          <label for="inspection_date" class="form-label fw-bold">วันที่การนิเทศ</label>
          <input type="date" id="inspection_date" name="inspection_date" class="form-control" required>
      </div>
  </div>

  <hr class="my-5">

  <!-- ================================================== -->
  <!-- ===== ส่วนของตัวชี้วัดและคำถาม (ของเดิม) ===== -->
  <!-- ================================================== -->

  <?php foreach ($indicators as $indicator_id => $indicator_data) : ?>
    <div class="section-header mb-3">
      <h2 class="h5"><?php echo htmlspecialchars($indicator_data['title']); ?></h2>
    </div>

    <?php if (!empty($indicator_data['questions'])) : ?>
      <?php foreach ($indicator_data['questions'] as $question) :
        $question_id = $question['question_id'];
      ?>
        <div class="card mb-3">
          <div class="card-body p-4">
            <div class="mb-3">
              <label class="form-label-question" for="rating_<?php echo $question_id; ?>">
                <?php echo htmlspecialchars($question['question_text']); ?>
              </label>
            </div>
            <p>เลือกคะแนนตามความพึงพอใจของคุณ</p>

            <?php for ($i = 3; $i >= 0; $i--) : ?>
              <div class="form-check form-check-inline">
                <input
                  class="form-check-input"
                  type="radio"
                  name="ratings[<?php echo $question_id; ?>]"
                  id="q<?php echo $question_id; ?>-<?php echo $i; ?>"
                  value="<?php echo $i; ?>"
                  required
                  <?php echo ($i == 3) ? 'checked' : ''; ?> /> <label class="form-check-label" for="q<?php echo $question_id; ?>-<?php echo $i; ?>"><?php echo $i; ?></label>
              </div>
            <?php endfor; ?>

            <hr class="my-4" />
            <div class="mb-3">
              <label for="comments_<?php echo $question_id; ?>" class="form-label">ข้อค้นพบ</label>
              <textarea
                class="form-control"
                id="comments_<?php echo $question_id; ?>"
                name="comments[<?php echo $question_id; ?>]"
                rows="3"
                placeholder="กรอกความคิดเห็นของคุณที่นี่...">-</textarea>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
      <!-- ส่วนสำหรับ "ข้อเสนอแนะเพิ่มเติม" ของแต่ละตัวชี้วัด -->
      <div class="card mb-4">
        <div class="card-body p-4">
          <div class="mb-3">
            <label for="indicator_suggestion_<?php echo $indicator_id; ?>" class="form-label fw-bold">ข้อเสนอแนะ</label>
            <textarea class="form-control" id="indicator_suggestion_<?php echo $indicator_id; ?>" name="indicator_suggestions[<?php echo $indicator_id; ?>]" rows="3" placeholder="กรอกข้อเสนอแนะ...">ทดสอบข้อมูล</textarea>
          </div>
        </div>
      </div>
    <?php endif; ?>
  <?php endforeach; ?>

  <!-- ================================================== -->
  <!-- ===== ส่วนของข้อเสนอแนะภาพรวม (ของเดิม) ===== -->
  <!-- ================================================== -->
  <div class="card mt-4 border-primary">
    <div class="card-header bg-primary text-white fw-bold">ข้อเสนอแนะเพิ่มเติม</div>
    <div class="card-body">
      <textarea class="form-control" id="overall_suggestion" name="overall_suggestion" rows="4" placeholder="กรอกข้อเสนอแนะเพิ่มเติมเกี่ยวกับการนิเทศครั้งนี้...">-</textarea>
    </div>
  </div>

  <div class="d-flex justify-content-center my-4">
    <button type="submit" class="btn btn-success fs-5 btn-hover-blue px-4 py-2">
      บันทึกข้อมูล
    </button>
  </div>
</form>

<script>
  // JavaScript Function สำหรับตรวจสอบฟอร์มก่อนบันทึก
  function validateKpiForm() {
    const subjectCode = document.getElementById('subject_code').value;
    const subjectName = document.getElementById('subject_name').value;
    const inspectionTime = document.getElementById('inspection_time').value;
    const inspectionDate = document.getElementById('inspection_date').value;

    // ตรวจสอบว่ากรอกข้อมูลการนิเทศครบหรือไม่
    if (!subjectCode || !subjectName || !inspectionTime || !inspectionDate) {
      alert('กรุณากรอกข้อมูลการนิเทศ (รหัสวิชา, ชื่อวิชา, ครั้งที่, วันที่) ให้ครบถ้วน');
      // เลื่อนหน้าจอไปยังช่องที่กรอกไม่ครบช่องแรก
      document.getElementById('subject_code').focus();
      return false;
    }

    // หากทุกอย่างถูกต้อง สามารถส่งฟอร์มได้
    return true;
  }
</script>