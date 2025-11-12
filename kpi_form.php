<!DOCTYPE html>
<html lang="th">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>แบบฟอร์มประเมิน</title>

    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    
    <link href="styles.css" rel="stylesheet" />

    </head>
  <body>
    <div class="container mt-5">
      <div class="section-header mb-3">
        <h2 class="h5">
          ตัวชี้วัดที่ 1 ผู้เรียนสามารถ เข้าถึงสิ่งเรียนและ เข้าใจบทเรียน /
          กิจกรรม
        </h2>
      </div>

      <div class="card mb-3">
        <div class="card-body p-4">
          <form id="evaluationForm">
            <div class="mb-3">
              <label class="form-label-question"
                >เนื้อหา (Content) พร้อมโน้ตทัศน์ที่จัดให้ผู้เรียนเรียนรู้
                หรือฝึกฝน มีความถูกต้อง และ ตรงตามหลักสูตร</label
              >
            </div>
            <p>เลือกคะแนนตามความพึงพอใจของคุณ</p>
            <div class="form-check form-check-inline">
              <input
                class="form-check-input"
                type="radio"
                name="contentRating"
                id="q1-3"
                value="3"
              />
              <label class="form-check-label" for="q1-3">3</label>
            </div>
            <div class="form-check form-check-inline">
              <input
                class="form-check-input"
                type="radio"
                name="contentRating"
                id="q1-2"
                value="2"
              />
              <label class="form-check-label" for="q1-2">2</label>
            </div>
            <div class="form-check form-check-inline">
              <input
                class="form-check-input"
                type="radio"
                name="contentRating"
                id="q1-1"
                value="1"
              />
              <label class="form-check-label" for="q1-1">1</label>
            </div>
            <div class="form-check form-check-inline">
              <input
                class="form-check-input"
                type="radio"
                name="contentRating"
                id="q1-0"
                value="0"
              />
              <label class="form-check-label" for="q1-0">0</label>
            </div>

            <hr class="my-4" />
            <div class="mb-3">
              <label for="comments" class="form-label">ข้อค้นพบ</label>
              <textarea
                class="form-control"
                id="comments"
                name="comments"
                rows="3"
                placeholder="กรอกความคิดเห็นของคุณที่นี่..."
              ></textarea>
            </div>
          </form>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-body p-4">
          <form id="evaluationForm">
            <div class="mb-3">
              <label class="form-label-question"
                >ออกแบบและจัดโครงสร้างบทเรียนเป็นระบบและใช้เวลาเหมาะสม</label
              >
            </div>
            <p>เลือกคะแนนตามความพึงพอใจของคุณ</p>
            <div class="form-check form-check-inline">
              <input
                class="form-check-input"
                type="radio"
                name="contentRating"
                id="q1-3"
                value="3"
              />
              <label class="form-check-label" for="q1-3">3</label>
            </div>
            <div class="form-check form-check-inline">
              <input
                class="form-check-input"
                type="radio"
                name="contentRating"
                id="q1-2"
                value="2"
              />
              <label class="form-check-label" for="q1-2">2</label>
            </div>
            <div class="form-check form-check-inline">
              <input
                class="form-check-input"
                type="radio"
                name="contentRating"
                id="q1-1"
                value="1"
              />
              <label class="form-check-label" for="q1-1">1</label>
            </div>
            <div class="form-check form-check-inline">
              <input
                class="form-check-input"
                type="radio"
                name="contentRating"
                id="q1-0"
                value="0"
              />
              <label class="form-check-label" for="q1-0">0</label>
            </div>

            <hr class="my-4" />
            <div class="mb-3">
              <label for="comments" class="form-label">ข้อค้นพบ</label>
              <textarea
                class="form-control"
                id="comments"
                name="comments"
                rows="3"
                placeholder="กรอกความคิดเห็นของคุณที่นี่..."
              ></textarea>
            </div>
          </form>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-body p-4">
          <form id="evaluationForm">
            <div class="mb-3">
              <label class="form-label-question"
                >ใช้สื่อประกอบบทเรียนได้เหมาะสมและช่วยในการเรียนรู้บรรลุวัตถุประสงค์ของบทเรียน</label
              >
            </div>
            <p>เลือกคะแนนตามความพึงพอใจของคุณ</p>
            <div class="form-check form-check-inline">
              <input
                class="form-check-input"
                type="radio"
                name="contentRating"
                id="q1-3"
                value="3"
              />
              <label class="form-check-label" for="q1-3">3</label>
            </div>
            <div class="form-check form-check-inline">
              <input
                class="form-check-input"
                type="radio"
                name="contentRating"
                id="q1-2"
                value="2"
              />
              <label class="form-check-label" for="q1-2">2</label>
            </div>
            <div class="form-check form-check-inline">
              <input
                class="form-check-input"
                type="radio"
                name="contentRating"
                id="q1-1"
                value="1"
              />
              <label class="form-check-label" for="q1-1">1</label>
            </div>
            <div class="form-check form-check-inline">
              <input
                class="form-check-input"
                type="radio"
                name="contentRating"
                id="q1-0"
                value="0"
              />
              <label class="form-check-label" for="q1-0">0</label>
            </div>

            <hr class="my-4" />
            <div class="mb-3">
              <label for="comments" class="form-label">ข้อค้นพบ</label>
              <textarea
                class="form-control"
                id="comments"
                name="comments"
                rows="3"
                placeholder="กรอกความคิดเห็นของคุณที่นี่..."
              ></textarea>
            </div>
          </form>
        </div>
      </div>

      <div class="mb-3 p-2">
        <label for="comments" class="form-label">ข้อเสนอแนะ</label>
        <textarea
          class="form-control"
          id="comments"
          name="comments"
          rows="3"
          placeholder="กรอกความคิดเห็นของคุณที่นี่..."
        ></textarea>
      </div>

      <div class="d-flex justify-content-center">
        <button
          type="button"
          onclick="changeBackgroundColor('')"
          class="btn btn-success fs-1 btn-hover-blue"
        >
          บันทึกข้อมูล
        </button>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
      document.addEventListener("DOMContentLoaded", function () {
        // เลือก input 'radio' ทั้งหมดในฟอร์ม
        const allRadioButtons = document.querySelectorAll(
          '#evaluationForm input[type="radio"]'
        );

        allRadioButtons.forEach(function (radio) {
          radio.addEventListener("change", function () {
            console.log(`คำถาม: ${this.name}, ค่าที่เลือก: ${this.value}`);
          });
        });

        // (ส่วนเสริม) ดักจับการพิมพ์ใน Textarea
        const commentsBox = document.getElementById("comments");
        if (commentsBox) {
          commentsBox.addEventListener("input", function () {
            console.log(`กำลังพิมพ์ข้อเสนอแนะ: ${this.value}`);
          });
        }
      });
      // function เปลี่ยนสีปุ่ม
      function changeBackgroundColor(color) {
        document.body.style.backgroundColor = color; // Changes the body's background
      }
    </script>
  </body>
</html>