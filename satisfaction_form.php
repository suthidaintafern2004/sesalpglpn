<?php
// ไฟล์: satisfaction_form.php (จะถูก include ใน satisfaction_summary.php)

// ดึงข้อมูลจาก Session ที่ถูกตั้งค่าไว้ในหน้า summary
$satisfaction_data = $_SESSION['satisfaction_data'] ?? [];

// กำหนดคำถามสำหรับแบบประเมิน
$questions = [
    1 => "การออกแบบและความสวยงามของระบบโดยรวม",
    2 => "ความง่ายในการเข้าถึงและใช้งานเมนูต่างๆ",
    3 => "ความรวดเร็วในการตอบสนองของระบบ",
    4 => "ประโยชน์ของข้อมูลที่ได้รับจากรายงานผลการนิเทศ",
    5 => "ความพึงพอใจต่อภาพรวมของระบบสารสนเทศเพื่อการนิเทศ (SESA)"
];

?>
<!-- แบบฟอร์มหลัก -->
<form id="satisfactionForm" method="POST" action="save_satisfaction.php">

    <!-- ส่วนแสดงข้อมูลการนิเทศ -->
    <h4 class="fw-bold text-primary">ข้อมูลการนิเทศ</h4>
    <div class="row mb-4">
        <div class="col-md-6">
            <strong>ผู้นิเทศ:</strong> <?php echo htmlspecialchars($satisfaction_data['supervisor_name'] ?? 'N/A'); ?>
        </div>
        <div class="col-md-6">
            <strong>ผู้รับการนิเทศ:</strong> <?php echo htmlspecialchars($satisfaction_data['teacher_name'] ?? 'N/A'); ?>
        </div>
    </div>

    <hr class="my-5">

    <!-- ส่วนของคำถามประเมิน -->
    <div class="section-header mb-3">
        <h2 class="h5">ประเด็นการประเมินความพึงพอใจ</h2>
    </div>

    <?php foreach ($questions as $q_id => $q_text) : ?>
        <div class="card mb-3">
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label-question" for="rating_<?php echo $q_id; ?>">
                        <?php echo htmlspecialchars($q_text); ?>
                    </label>
                </div>
                <p>ระดับความพึงพอใจ (5 = มากที่สุด, 1 = น้อยที่สุด)</p>

                <div class="d-flex justify-content-center">
                    <?php for ($i = 5; $i >= 1; $i--) : ?>
                        <div class="form-check form-check-inline mx-2">
                            <input
                                class="form-check-input"
                                type="radio"
                                name="ratings[<?php echo $q_id; ?>]"
                                id="q<?php echo $q_id; ?>-<?php echo $i; ?>"
                                value="<?php echo $i; ?>"
                                required
                                <?php echo ($i == 5) ? 'checked' : ''; // ให้คะแนน 5 เป็นค่าเริ่มต้น ?>
                            />
                            <label class="form-check-label" for="q<?php echo $q_id; ?>-<?php echo $i; ?>"><?php echo $i; ?></label>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- ส่วนสำหรับ "ข้อเสนอแนะเพิ่มเติม" -->
    <div class="card mt-4 border-primary">
        <div class="card-header bg-primary text-white fw-bold">
            <i class="fas fa-lightbulb"></i> ข้อเสนอแนะเพิ่มเติมเพื่อการพัฒนาระบบ
        </div>
        <div class="card-body">
            <textarea
                class="form-control"
                id="overall_suggestion"
                name="overall_suggestion"
                rows="4"
                placeholder="กรอกข้อเสนอแนะของคุณที่นี่..."></textarea>
        </div>
    </div>

    <!-- ปุ่มบันทึกข้อมูล -->
    <div class="d-flex justify-content-center my-4">
        <button type="submit" class="btn btn-success fs-5 btn-hover-blue px-4 py-2">
            <i class="fas fa-save"></i> บันทึกข้อมูลความพึงพอใจ
        </button>
    </div>
</form>