<?php
// ไฟล์: comparison_bar_chart.php
// ส่วนแสดงผลของกราฟเปรียบเทียบผลการประเมิน 2 แบบฟอร์ม
?>
<div class="card shadow-sm">
    <div class="card-header card-header-custom text-center">
        <h2 class="h4 mb-0"><i class="fas fa-chart-bar"></i> กราฟเปรียบเทียบผลการประเมิน</h2>
    </div>
    <div class="card-body p-4">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h5 class="card-title text-center mb-3">คะแนนเฉลี่ยรายข้อของแต่ละแบบฟอร์ม</h5>
                <canvas id="comparisonChart" style="max-height: 450px;"></canvas>
            </div>

            <!-- ส่วนของตารางข้อมูลเปรียบเทียบ -->
            <div class="col-lg-6">
                <h5 class="card-title text-center mb-3">ตารางสรุปข้อมูลดิบ</h5>
                <table class="table table-striped table-hover table-bordered">
                    <thead class="table-info">
                        <tr class="text-center">
                            <th scope="col">ประเด็นการประเมิน</th>
                            <th scope="col">คะแนน (แบบฟอร์ม 1)</th>
                            <th scope="col">คะแนน (แบบฟอร์ม 2)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($i = 0; $i < count($form1_data); $i++): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(preg_replace('/^\d+\.\s*/', '', $form1_data[$i]['question_text_with_number'])); ?></td>
                                <td class="text-center"><?php echo number_format($form1_data[$i]['average_score'], 2); ?></td>
                                <td class="text-center"><?php echo isset($form2_data[$i]) ? number_format($form2_data[$i]['average_score'], 2) : 'N/A'; ?></td>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // --- กราฟเปรียบเทียบผลการประเมิน (Bar Chart) ---
    const ctx = document.getElementById('comparisonChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo $chart_labels; ?>, // ["1. ด้าน...", "2. ด้าน..."]
            datasets: [{
                label: 'แบบฟอร์มประเมินตนเอง',
                data: <?php echo $form1_scores_js; ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }, {
                label: 'แบบฟอร์มประเมินโดยผู้บังคับบัญชา',
                data: <?php echo $form2_scores_js; ?>,
                backgroundColor: 'rgba(255, 99, 132, 0.7)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true, max: 5 } }, // เริ่มแกน Y ที่ 0 และสูงสุดที่ 5
            plugins: { legend: { position: 'top' } }
        }
    });
});
</script>